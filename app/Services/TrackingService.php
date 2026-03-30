<?php
namespace App\Services;

use App\Models\Email;
use App\Models\EmailEvent;

class TrackingService
{
    public function injectTrackingPixel(string $html, string $emailId): string
    {
        $pixel = '<img src="' . url("/t/o/{$emailId}") . '" width="1" height="1" style="display:none;" alt="" />';
        return str_replace('</body>', $pixel . '</body>', $html) ?: $html . $pixel;
    }

    public function rewriteLinks(string $html, string $emailId): string
    {
        return preg_replace_callback(
            '/<a\s([^>]*?)href=["\']([^"\']+?)["\']/i',
            function ($matches) use ($emailId) {
                $url = $matches[2];
                if (str_starts_with($url, '#') || str_contains($url, '/t/c/')) return $matches[0];
                $tracked = url("/t/c/{$emailId}") . '?url=' . urlencode($url);
                return '<a ' . $matches[1] . 'href="' . $tracked . '"';
            },
            $html
        );
    }

    public function addUtmParams(string $html, array $utm): string
    {
        if (empty($utm['source'])) return $html;

        $params = http_build_query(array_filter([
            'utm_source' => $utm['source'] ?? null,
            'utm_medium' => $utm['medium'] ?? 'email',
            'utm_campaign' => $utm['campaign'] ?? null,
        ]));

        return preg_replace_callback(
            '/<a\s([^>]*?)href=["\']([^"\'#]+?)["\']/i',
            function ($matches) use ($params) {
                $url = $matches[2];
                if (str_contains($url, 'unsubscribe') || str_starts_with($url, 'mailto:')) return $matches[0];
                $sep = str_contains($url, '?') ? '&' : '?';
                return '<a ' . $matches[1] . 'href="' . $url . $sep . $params . '"';
            },
            $html
        );
    }

    public function recordOpen(string $emailId, ?string $ip = null, ?string $userAgent = null): void
    {
        $email = Email::find($emailId);
        if (!$email) return;

        EmailEvent::create(['email_id' => $emailId, 'type' => 'opened', 'ip' => $ip, 'user_agent' => $userAgent]);
        $email->increment('opens_count');
        if (!$email->opened_at) $email->update(['opened_at' => now()]);
    }

    public function recordClick(string $emailId, string $url, ?string $ip = null, ?string $userAgent = null): void
    {
        $email = Email::find($emailId);
        if (!$email) return;

        EmailEvent::create(['email_id' => $emailId, 'type' => 'clicked', 'url' => $url, 'ip' => $ip, 'user_agent' => $userAgent]);
        $email->increment('clicks_count');
        if (!$email->clicked_at) $email->update(['clicked_at' => now()]);
    }
}
