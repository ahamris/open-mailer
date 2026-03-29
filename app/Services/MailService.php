<?php

namespace App\Services;

use App\Models\Email;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;

class MailService
{
    public function send(array $data, ?string $apiKeyId = null): Email
    {
        // Idempotency check
        if (!empty($data['idempotency_key'])) {
            $existing = Email::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // Parse from address
        $fromParsed = $this->parseFromAddress($data['from']);

        $email = Email::create([
            'api_key_id' => $apiKeyId,
            'direction' => 'outbound',
            'status' => !empty($data['scheduled_at']) ? 'scheduled' : 'queued',
            'from_address' => $fromParsed['email'],
            'from_name' => $fromParsed['name'],
            'to_addresses' => (array) $data['to'],
            'cc_addresses' => isset($data['cc']) ? (array) $data['cc'] : null,
            'bcc_addresses' => isset($data['bcc']) ? (array) $data['bcc'] : null,
            'reply_to' => isset($data['reply_to']) ? (array) $data['reply_to'] : null,
            'subject' => $data['subject'],
            'html_body' => $data['html'] ?? null,
            'text_body' => $data['text'] ?? null,
            'headers' => $data['headers'] ?? null,
            'tags' => $data['tags'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'message_id' => '<' . Str::uuid() . '@clom.code-labs.nl>',
        ]);

        if ($email->status === 'queued') {
            SendEmailJob::dispatch($email);
        }

        return $email;
    }

    public function sendBatch(array $emails, ?string $apiKeyId = null): array
    {
        $results = [];
        foreach ($emails as $data) {
            $results[] = $this->send($data, $apiKeyId);
        }
        return $results;
    }

    private function parseFromAddress(string $from): array
    {
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $from, $matches)) {
            return ['name' => trim($matches[1]), 'email' => trim($matches[2])];
        }
        return ['name' => null, 'email' => trim($from)];
    }
}
