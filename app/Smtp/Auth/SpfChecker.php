<?php

namespace App\Smtp\Auth;

class SpfChecker
{
    public static function check(string $ip, string $sender, int $depth = 0): string
    {
        if ($depth > 10) return 'permerror';

        $domain = self::getDomain($sender);
        if (!$domain) return 'none';

        $records = @dns_get_record($domain, DNS_TXT) ?: [];
        $spfRecord = null;
        foreach ($records as $record) {
            if (str_starts_with($record['txt'] ?? '', 'v=spf1')) {
                $spfRecord = $record['txt'];
                break;
            }
        }

        if (!$spfRecord) return 'none';

        return self::evaluate($ip, $spfRecord, $domain, $depth);
    }

    private static function evaluate(string $ip, string $record, string $domain, int $depth): string
    {
        if ($depth > 10) return 'permerror';

        $mechanisms = preg_split('/\s+/', $record);
        array_shift($mechanisms); // Remove "v=spf1"

        foreach ($mechanisms as $mechanism) {
            $qualifier = '+';
            if (in_array($mechanism[0] ?? '', ['+', '-', '~', '?'])) {
                $qualifier = $mechanism[0];
                $mechanism = substr($mechanism, 1);
            }

            $result = match ($qualifier) {
                '+' => 'pass', '-' => 'fail', '~' => 'softfail', '?' => 'neutral',
            };

            if ($mechanism === 'all') return $result;

            if (str_starts_with($mechanism, 'ip4:')) {
                if (self::ipInRange($ip, substr($mechanism, 4))) return $result;
            }

            if (str_starts_with($mechanism, 'ip6:')) {
                if (self::ipInRange($ip, substr($mechanism, 4))) return $result;
            }

            if (str_starts_with($mechanism, 'include:')) {
                $includeResult = self::check($ip, "check@" . substr($mechanism, 8), $depth + 1);
                if ($includeResult === 'pass') return $result;
            }

            if (str_starts_with($mechanism, 'a')) {
                $target = str_contains($mechanism, ':') ? explode(':', $mechanism)[1] : $domain;
                foreach (@dns_get_record($target, DNS_A) ?: [] as $r) {
                    if (($r['ip'] ?? '') === $ip) return $result;
                }
            }

            if (str_starts_with($mechanism, 'mx')) {
                $target = str_contains($mechanism, ':') ? explode(':', $mechanism)[1] : $domain;
                foreach (@dns_get_record($target, DNS_MX) ?: [] as $mx) {
                    foreach (@dns_get_record($mx['target'], DNS_A) ?: [] as $r) {
                        if (($r['ip'] ?? '') === $ip) return $result;
                    }
                }
            }

            if (str_starts_with($mechanism, 'redirect=')) {
                return self::check($ip, "check@" . substr($mechanism, 9), $depth + 1);
            }
        }

        return 'neutral';
    }

    private static function getDomain(string $email): ?string
    {
        return explode('@', $email)[1] ?? null;
    }

    private static function ipInRange(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) return $ip === $cidr;
        [$subnet, $mask] = explode('/', $cidr);
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return false;
        return (ip2long($ip) & ~((1 << (32 - (int) $mask)) - 1))
            === (ip2long($subnet) & ~((1 << (32 - (int) $mask)) - 1));
    }
}
