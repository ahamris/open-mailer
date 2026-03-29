<?php

namespace App\Smtp;

class Envelope
{
    public string $heloHostname = '';
    public string $peerAddress = '';
    public string $fromAddress = '';
    public array $recipients = [];
    public string $rawHeaders = '';
    public string $rawBody = '';
    public array $parsedHeaders = [];
    public array $bodyParts = [];

    public function parseHeaders(): void
    {
        $lines = explode("\r\n", $this->rawHeaders);
        $currentKey = '';
        $headers = [];

        foreach ($lines as $line) {
            if ($line === '') continue;

            if (preg_match('/^\s/', $line)) {
                // Folded header continuation
                if ($currentKey) {
                    $headers[$currentKey] .= ' ' . trim($line);
                }
            } elseif (preg_match('/^([^:]+):\s*(.*)/', $line, $matches)) {
                $currentKey = strtolower($matches[1]);
                $headers[$currentKey] = trim($matches[2]);
            }
        }

        // Parse specifieke headers
        if (isset($headers['from'])) {
            $headers['from'] = $this->parseEmailAddress($headers['from']);
        }
        if (isset($headers['to'])) {
            $headers['to'] = $this->parseEmailAddressList($headers['to']);
        }
        if (isset($headers['content-type'])) {
            $headers['content-type'] = $this->parseContentType($headers['content-type']);
        }

        $this->parsedHeaders = $headers;
        $this->parseBody();
    }

    private function parseBody(): void
    {
        $contentType = $this->parsedHeaders['content-type'] ?? [];
        $type = $contentType['type'] ?? 'text/plain';
        $boundary = $contentType['boundary'] ?? null;

        if ($boundary && str_starts_with($type, 'multipart/')) {
            $this->bodyParts = $this->parseMultipart($this->rawBody, $boundary);
        } else {
            $encoding = $this->parsedHeaders['content-transfer-encoding'] ?? '7bit';
            $this->bodyParts[] = [
                'type' => $type,
                'content' => $this->decodeBody($this->rawBody, $encoding),
            ];
        }
    }

    private function parseMultipart(string $body, string $boundary): array
    {
        $parts = [];
        $sections = explode("--{$boundary}", $body);

        foreach ($sections as $section) {
            $section = trim($section);
            if ($section === '' || $section === '--') continue;

            $split = strpos($section, "\r\n\r\n");
            if ($split === false) {
                $split = strpos($section, "\n\n");
            }
            if ($split === false) continue;

            $headerBlock = substr($section, 0, $split);
            $bodyBlock = substr($section, $split + (strpos($section, "\r\n\r\n") !== false ? 4 : 2));

            $headers = [];
            foreach (explode("\r\n", $headerBlock) as $line) {
                if (preg_match('/^([^:]+):\s*(.*)/', $line, $m)) {
                    $headers[strtolower($m[1])] = trim($m[2]);
                }
            }

            $partType = $this->parseContentType($headers['content-type'] ?? 'text/plain');
            $encoding = $headers['content-transfer-encoding'] ?? '7bit';

            if (str_starts_with($partType['type'] ?? '', 'multipart/') && isset($partType['boundary'])) {
                $parts = array_merge($parts, $this->parseMultipart($bodyBlock, $partType['boundary']));
            } else {
                $parts[] = [
                    'type' => $partType['type'] ?? 'text/plain',
                    'content' => $this->decodeBody($bodyBlock, $encoding),
                    'headers' => $headers,
                ];
            }
        }

        return $parts;
    }

    private function decodeBody(string $body, string $encoding): string
    {
        return match (strtolower($encoding)) {
            'base64' => base64_decode($body),
            'quoted-printable' => quoted_printable_decode($body),
            default => $body,
        };
    }

    public function getHtmlBody(): ?string
    {
        foreach ($this->bodyParts as $part) {
            if (($part['type'] ?? '') === 'text/html') {
                return $part['content'];
            }
        }
        return null;
    }

    public function getTextBody(): ?string
    {
        foreach ($this->bodyParts as $part) {
            if (($part['type'] ?? '') === 'text/plain') {
                return $part['content'];
            }
        }
        return null;
    }

    private function parseEmailAddress(string $value): array
    {
        if (preg_match('/^"?(.+?)"?\s*<(.+?)>/', $value, $matches)) {
            return ['name' => trim($matches[1], '" '), 'email' => $matches[2]];
        }
        return ['name' => null, 'email' => trim($value, '<> ')];
    }

    private function parseEmailAddressList(string $value): array
    {
        return array_map(fn ($v) => $this->parseEmailAddress(trim($v)), explode(',', $value));
    }

    private function parseContentType(string $value): array
    {
        $parts = array_map('trim', explode(';', $value));
        $result = ['type' => $parts[0]];

        for ($i = 1; $i < count($parts); $i++) {
            if (preg_match('/^(\w+)=(.+)/', $parts[$i], $m)) {
                $result[$m[1]] = trim($m[2], '"');
            }
        }

        return $result;
    }
}
