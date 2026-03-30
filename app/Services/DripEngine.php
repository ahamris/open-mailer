<?php
namespace App\Services;

use App\Models\DripCampaign;
use App\Models\DripEnrollment;
use App\Models\DripStep;
use App\Models\Contact;
use App\Models\Email;

class DripEngine
{
    public function __construct(private MailService $mailService) {}

    public function enroll(DripCampaign $campaign, Contact $contact): ?DripEnrollment
    {
        if (!$campaign->active) return null;
        if ($contact->unsubscribed) return null;

        $enrollment = DripEnrollment::firstOrCreate(
            ['drip_campaign_id' => $campaign->id, 'contact_id' => $contact->id],
            ['current_step' => 0, 'status' => 'active', 'next_action_at' => now()]
        );

        if ($enrollment->wasRecentlyCreated) {
            $campaign->increment('enrolled_count');
        }

        return $enrollment;
    }

    public function processEnrollments(): int
    {
        $processed = 0;
        $enrollments = DripEnrollment::where('status', 'active')
            ->where('next_action_at', '<=', now())
            ->with(['campaign.steps', 'contact'])
            ->limit(100)
            ->get();

        foreach ($enrollments as $enrollment) {
            $this->processStep($enrollment);
            $processed++;
        }

        return $processed;
    }

    private function processStep(DripEnrollment $enrollment): void
    {
        $campaign = $enrollment->campaign;
        $steps = $campaign->steps;
        $contact = $enrollment->contact;

        if ($enrollment->current_step >= $steps->count()) {
            $enrollment->update(['status' => 'completed']);
            $campaign->increment('completed_count');
            return;
        }

        $step = $steps[$enrollment->current_step];

        match ($step->type) {
            'email' => $this->sendEmail($step, $campaign, $contact, $enrollment),
            'delay' => $this->applyDelay($step, $enrollment),
            'condition' => $this->evaluateCondition($step, $contact, $enrollment),
            default => $this->advanceStep($enrollment),
        };
    }

    private function sendEmail(DripStep $step, DripCampaign $campaign, Contact $contact, DripEnrollment $enrollment): void
    {
        $html = $step->html_body ?? $step->template?->html_body ?? '';
        $subject = $step->subject ?? $step->template?->subject ?? 'No subject';

        $html = $this->replaceShortcodes($html, $contact);
        $subject = $this->replaceShortcodes($subject, $contact);

        try {
            $this->mailService->send([
                'from' => $campaign->from_name ? "{$campaign->from_name} <{$campaign->from_address}>" : $campaign->from_address,
                'to' => $contact->email,
                'subject' => $subject,
                'html' => $html,
            ]);
            $step->increment('sent_count');
        } catch (\Exception $e) {
            // Log but continue
        }

        $this->advanceStep($enrollment);
    }

    private function applyDelay(DripStep $step, DripEnrollment $enrollment): void
    {
        $delay = now()->addDays($step->delay_days)->addHours($step->delay_hours);
        $enrollment->update([
            'current_step' => $enrollment->current_step + 1,
            'next_action_at' => $delay,
        ]);
    }

    private function evaluateCondition(DripStep $step, Contact $contact, DripEnrollment $enrollment): void
    {
        $pass = match ($step->condition_field) {
            'has_tag' => $contact->tags()->where('name', $step->condition_value)->exists(),
            'is_confirmed' => $contact->confirmed,
            default => true,
        };

        if ($pass) {
            $this->advanceStep($enrollment);
        } else {
            // Skip to next step after condition
            $enrollment->update([
                'current_step' => $enrollment->current_step + 2,
                'next_action_at' => now(),
            ]);
        }
    }

    private function advanceStep(DripEnrollment $enrollment): void
    {
        $enrollment->update([
            'current_step' => $enrollment->current_step + 1,
            'next_action_at' => now(),
        ]);
    }

    private function replaceShortcodes(string $content, Contact $contact): string
    {
        return str_replace(
            ['{{contact.email}}', '{{contact.first_name}}', '{{contact.last_name}}', '{{contact.full_name}}', '{{date}}', '{{company}}'],
            [$contact->email, $contact->first_name ?? '', $contact->last_name ?? '', $contact->full_name, now()->format('F j, Y'), config('app.name')],
            $content
        );
    }
}
