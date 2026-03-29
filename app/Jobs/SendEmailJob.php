<?php

namespace App\Jobs;

use App\Models\Email;
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

    public function handle(): void
    {
        $email = $this->email;
        $email->update(['status' => 'sending']);

        try {
            Mail::raw('', function (Message $message) use ($email) {
                $message->from($email->from_address, $email->from_name);
                $message->subject($email->subject);

                foreach ($email->to_addresses as $to) {
                    $message->to($to);
                }

                if ($email->cc_addresses) {
                    foreach ($email->cc_addresses as $cc) {
                        $message->cc($cc);
                    }
                }

                if ($email->bcc_addresses) {
                    foreach ($email->bcc_addresses as $bcc) {
                        $message->bcc($bcc);
                    }
                }

                if ($email->reply_to) {
                    foreach ($email->reply_to as $replyTo) {
                        $message->replyTo($replyTo);
                    }
                }

                if ($email->html_body) {
                    $message->html($email->html_body);
                } elseif ($email->text_body) {
                    $message->text($email->text_body);
                }

                if ($email->headers) {
                    foreach ($email->headers as $key => $value) {
                        $message->getHeaders()->addTextHeader($key, $value);
                    }
                }
            });

            $email->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $email->update([
                'status' => 'failed',
                'bounce_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
