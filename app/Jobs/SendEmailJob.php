<?php
namespace App\Jobs;

use App\Models\Email;
use App\Models\Suppression;
use App\Services\TrackingService;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(public Email $email) {}

    public function handle(TrackingService $tracking, WebhookService $webhooks): void
    {
        $email = $this->email;

        // Check suppression list
        foreach ($email->to_addresses as $to) {
            if (Suppression::isSuppressed($to)) {
                $email->update(['status' => 'suppressed', 'bounce_reason' => "Suppressed: {$to}"]);
                return;
            }
        }

        $email->update(['status' => 'sending']);

        try {
            $htmlBody = $email->html_body;

            // Inject tracking
            if ($htmlBody && $email->track_opens) {
                $htmlBody = $tracking->injectTrackingPixel($htmlBody, $email->id);
            }
            if ($htmlBody && $email->track_clicks) {
                $htmlBody = $tracking->rewriteLinks($htmlBody, $email->id);
            }

            Mail::raw('', function (Message $message) use ($email, $htmlBody) {
                $message->from($email->from_address, $email->from_name);
                $message->subject($email->subject);

                foreach ($email->to_addresses as $to) { $message->to($to); }
                if ($email->cc_addresses) { foreach ($email->cc_addresses as $cc) { $message->cc($cc); } }
                if ($email->bcc_addresses) { foreach ($email->bcc_addresses as $bcc) { $message->bcc($bcc); } }
                if ($email->reply_to) { foreach ($email->reply_to as $r) { $message->replyTo($r); } }

                if ($htmlBody) { $message->html($htmlBody); }
                elseif ($email->text_body) { $message->text($email->text_body); }

                if ($email->headers) { foreach ($email->headers as $k => $v) { $message->getHeaders()->addTextHeader($k, $v); } }
            });

            $email->update(['status' => 'sent', 'sent_at' => now()]);
            $webhooks->dispatch('email.sent', ['id' => $email->id, 'to' => $email->to_addresses, 'subject' => $email->subject]);

        } catch (\Exception $e) {
            $email->update(['status' => 'failed', 'bounce_reason' => $e->getMessage()]);
            $webhooks->dispatch('email.failed', ['id' => $email->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
