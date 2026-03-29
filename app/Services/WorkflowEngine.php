<?php
namespace App\Services;

use App\Models\Email;
use App\Models\Workflow;
use App\Models\WorkflowLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    public function __construct(
        private AiService $aiService,
        private MailService $mailService,
    ) {}

    public function processIncoming(Email $email): void
    {
        $workflows = Workflow::where('active', true)->orderBy('priority', 'desc')->get();

        foreach ($workflows as $workflow) {
            if ($this->matchesTriggers($email, $workflow->triggers)) {
                $this->executeActions($email, $workflow);
            }
        }
    }

    private function matchesTriggers(Email $email, array $triggers): bool
    {
        foreach ($triggers as $trigger) {
            $field = $trigger['field'] ?? '';
            $operator = $trigger['operator'] ?? 'contains';
            $value = $trigger['value'] ?? '';

            $emailValue = match ($field) {
                'from' => $email->from_address,
                'to' => implode(',', $email->to_addresses ?? []),
                'subject' => $email->subject,
                'body' => strip_tags($email->html_body ?? $email->text_body ?? ''),
                'has_attachment' => $email->attachments()->exists() ? 'true' : 'false',
                'spf' => $email->spf_result ?? '',
                'dkim' => $email->dkim_result ?? '',
                default => '',
            };

            $match = match ($operator) {
                'contains' => str_contains(strtolower($emailValue), strtolower($value)),
                'equals' => strtolower($emailValue) === strtolower($value),
                'starts_with' => str_starts_with(strtolower($emailValue), strtolower($value)),
                'ends_with' => str_ends_with(strtolower($emailValue), strtolower($value)),
                'regex' => (bool) preg_match("/{$value}/i", $emailValue),
                'is_true' => $emailValue === 'true',
                'is_false' => $emailValue === 'false',
                default => false,
            };

            if (!$match) return false;
        }

        return !empty($triggers);
    }

    private function executeActions(Email $email, Workflow $workflow): void
    {
        foreach ($workflow->actions as $action) {
            $type = $action['type'] ?? '';
            $status = 'success';
            $result = '';

            try {
                $result = match ($type) {
                    'label' => $this->actionLabel($email, $action),
                    'forward' => $this->actionForward($email, $action),
                    'auto_reply' => $this->actionAutoReply($email, $action),
                    'ai_reply' => $this->actionAiReply($email, $action),
                    'webhook' => $this->actionWebhook($email, $action),
                    'mark_read' => $this->actionMarkRead($email),
                    'star' => $this->actionStar($email),
                    'move' => $this->actionMove($email, $action),
                    default => "Onbekende actie: {$type}",
                };
            } catch (\Exception $e) {
                $status = 'failed';
                $result = $e->getMessage();
                Log::error("Workflow {$workflow->name} actie {$type} mislukt: {$result}");
            }

            WorkflowLog::create([
                'workflow_id' => $workflow->id,
                'email_id' => $email->id,
                'action' => $type,
                'status' => $status,
                'result' => $result,
            ]);
        }

        $workflow->increment('times_triggered');
        $workflow->update(['last_triggered_at' => now()]);
    }

    private function actionLabel(Email $email, array $action): string
    {
        $email->update(['folder' => $action['folder'] ?? 'labeled']);
        return "Label: {$action['folder']}";
    }

    private function actionForward(Email $email, array $action): string
    {
        $to = $action['to'] ?? '';
        $this->mailService->send([
            'from' => $email->from_address,
            'to' => $to,
            'subject' => "Fwd: {$email->subject}",
            'html' => "<p><em>Doorgestuurd via CLOM workflow</em></p><hr>" . ($email->html_body ?? $email->text_body ?? ''),
        ]);
        return "Doorgestuurd naar {$to}";
    }

    private function actionAutoReply(Email $email, array $action): string
    {
        $template = $action['template'] ?? 'Bedankt voor uw bericht. We nemen zo snel mogelijk contact met u op.';
        $this->mailService->send([
            'from' => $email->to_addresses[0] ?? config('mail.from.address'),
            'to' => $email->from_address,
            'subject' => "Re: {$email->subject}",
            'html' => $template,
        ]);
        return "Auto-reply verstuurd";
    }

    private function actionAiReply(Email $email, array $action): string
    {
        $instructions = $action['instructions'] ?? 'Reageer professioneel en behulpzaam.';
        $result = $this->aiService->compose($instructions, $email);
        $this->aiService->saveConversation($email, 'workflow_reply', $instructions, $result);

        if ($action['auto_send'] ?? false) {
            $this->mailService->send([
                'from' => $email->to_addresses[0] ?? config('mail.from.address'),
                'to' => $email->from_address,
                'subject' => "Re: {$email->subject}",
                'html' => nl2br($result['content']),
            ]);
            return "AI reply verstuurd";
        }

        return "AI reply concept opgeslagen";
    }

    private function actionWebhook(Email $email, array $action): string
    {
        $url = $action['url'] ?? '';
        $response = Http::timeout(10)->post($url, [
            'event' => 'email.received',
            'email' => [
                'id' => $email->id,
                'from' => $email->from_address,
                'to' => $email->to_addresses,
                'subject' => $email->subject,
                'text' => strip_tags($email->html_body ?? $email->text_body ?? ''),
            ],
        ]);
        return "Webhook: HTTP {$response->status()}";
    }

    private function actionMarkRead(Email $email): string
    {
        $email->update(['is_read' => true]);
        return "Gemarkeerd als gelezen";
    }

    private function actionStar(Email $email): string
    {
        $email->update(['is_starred' => true]);
        return "Ster toegevoegd";
    }

    private function actionMove(Email $email, array $action): string
    {
        $folder = $action['folder'] ?? 'archive';
        $email->update(['folder' => $folder]);
        return "Verplaatst naar {$folder}";
    }
}
