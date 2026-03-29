<?php

namespace App\Smtp\Auth;

use App\Smtp\Envelope;

class DkimVerifier
{
    private const REQUIRED_TAGS = ['v', 'a', 'b', 'bh', 'd', 'h', 's'];

    public static function verify(Envelope $envelope): array
    {
        $dkimHeader = $envelope->parsedHeaders['dkim-signature'] ?? null;

        if (!$dkimHeader) {
            return ['status' => 'none', 'reason' => 'No DKIM signature found'];
        }

        $tags = self::parseDkimSignature($dkimHeader);

        // Check required tags
        foreach (self::REQUIRED_TAGS as $tag) {
            if (!isset($tags[$tag])) {
                return ['status' => 'permfail', 'reason' => "Missing required tag: {$tag}"];
            }
        }

        if (($tags['v'] ?? '') !== '1') {
            return ['status' => 'permfail', 'reason' => 'Unsupported DKIM version'];
        }

        // Fetch public key via DNS
        $selector = $tags['s'];
        $domain = $tags['d'];
        $dnsQuery = "{$selector}._domainkey.{$domain}";

        $records = dns_get_record($dnsQuery, DNS_TXT);
        if (!$records) {
            return ['status' => 'tempfail', 'reason' => 'DNS lookup failed for DKIM key'];
        }

        $publicKeyData = '';
        foreach ($records as $record) {
            if (str_contains($record['txt'] ?? '', 'v=DKIM1')) {
                $publicKeyData = $record['txt'];
                break;
            }
        }

        if (!$publicKeyData) {
            return ['status' => 'permfail', 'reason' => 'No DKIM key found in DNS'];
        }

        // Extract public key
        if (preg_match('/p=([^;]+)/', $publicKeyData, $matches)) {
            $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
                      chunk_split(trim($matches[1]), 64, "\n") .
                      "-----END PUBLIC KEY-----";

            // Verify body hash
            $canonBody = self::canonicalizeBody($envelope->rawBody, $tags['c'] ?? 'simple/simple');
            $bodyHash = base64_encode(hash(self::getHashAlgo($tags['a']), $canonBody, true));

            if ($bodyHash !== trim($tags['bh'])) {
                return ['status' => 'permfail', 'reason' => 'Body hash mismatch'];
            }

            // Verify header signature
            $headerFields = array_map('trim', explode(':', strtolower($tags['h'])));
            $signData = self::buildSignData($envelope, $headerFields, $dkimHeader, $tags);

            $signature = base64_decode(preg_replace('/\s+/', '', $tags['b']));
            $keyResource = openssl_pkey_get_public($pubKey);

            if (!$keyResource) {
                return ['status' => 'permfail', 'reason' => 'Invalid public key'];
            }

            $algo = str_contains($tags['a'], 'sha256') ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;
            $valid = openssl_verify($signData, $signature, $keyResource, $algo);

            return $valid === 1
                ? ['status' => 'pass', 'reason' => 'DKIM signature verified']
                : ['status' => 'permfail', 'reason' => 'Signature verification failed'];
        }

        return ['status' => 'permfail', 'reason' => 'Could not parse public key'];
    }

    private static function parseDkimSignature(string $header): array
    {
        $tags = [];
        $parts = preg_split('/;\s*/', $header);
        foreach ($parts as $part) {
            if (preg_match('/^\s*(\w+)\s*=\s*(.*)/s', $part, $m)) {
                $tags[trim($m[1])] = trim($m[2]);
            }
        }
        return $tags;
    }

    private static function getHashAlgo(string $algorithm): string
    {
        return str_contains($algorithm, 'sha256') ? 'sha256' : 'sha1';
    }

    private static function canonicalizeBody(string $body, string $canon): string
    {
        $parts = explode('/', $canon);
        $bodyCanon = $parts[1] ?? $parts[0];

        if ($bodyCanon === 'relaxed') {
            $lines = explode("\r\n", $body);
            $lines = array_map(function ($line) {
                $line = preg_replace('/[ \t]+/', ' ', $line);
                return rtrim($line);
            }, $lines);
            $body = implode("\r\n", $lines);
        }

        $body = rtrim($body, "\r\n") . "\r\n";
        return $body;
    }

    private static function buildSignData(Envelope $envelope, array $fields, string $dkimHeader, array $tags): string
    {
        $parts = explode('/', $tags['c'] ?? 'simple/simple');
        $headerCanon = $parts[0] ?? 'simple';

        $data = '';
        $headerLines = explode("\r\n", $envelope->rawHeaders);
        $headerMap = [];

        foreach ($headerLines as $line) {
            if (preg_match('/^([^:]+):\s*(.*)/', $line, $m)) {
                $key = strtolower($m[1]);
                $headerMap[$key][] = $line;
            }
        }

        foreach ($fields as $field) {
            if (isset($headerMap[$field])) {
                $line = array_pop($headerMap[$field]);
                $data .= ($headerCanon === 'relaxed'
                    ? strtolower(preg_replace('/\s+/', ' ', $line))
                    : $line) . "\r\n";
            }
        }

        // Add DKIM header without b= value
        $dkimLine = 'dkim-signature:' . preg_replace('/b=[^;]*/', 'b=', $dkimHeader);
        if ($headerCanon === 'relaxed') {
            $dkimLine = preg_replace('/\s+/', ' ', $dkimLine);
        }
        $data .= $dkimLine;

        return $data;
    }
}
