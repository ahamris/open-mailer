<?php
namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::where('active', true)->get();

        foreach ($webhooks as $webhook) {
            if (!in_array($event, $webhook->events)) continue;

            try {
                $body = ['event' => $event, 'data' => $payload, 'timestamp' => now()->toISOString()];
                if ($webhook->secret) {
                    $body['signature'] = hash_hmac('sha256', json_encode($body), $webhook->secret);
                }
                Http::timeout(5)->post($webhook->url, $body);
                $webhook->increment('success_count');
                $webhook->update(['last_triggered_at' => now()]);
            } catch (\Exception $e) {
                $webhook->increment('failure_count');
            }
        }
    }
}
