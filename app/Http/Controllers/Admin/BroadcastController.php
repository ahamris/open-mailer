<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\Template;
use App\Models\Audience;
use App\Models\Suppression;
use App\Services\MailService;
use App\Services\TrackingService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function __construct(private MailService $mailService, private TrackingService $tracking) {}

    public function index()
    {
        return view('admin.broadcasts.index', [
            'broadcasts' => Broadcast::with(['template', 'audience'])->orderByDesc('created_at')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.broadcasts.form', [
            'broadcast' => null,
            'templates' => Template::orderBy('name')->get(),
            'audiences' => Audience::withCount('contacts')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'nullable|uuid',
            'audience_id' => 'required|uuid',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
            'subject' => 'required|string',
            'html_body' => 'nullable|string',
            'utm_tags' => 'nullable|boolean',
            'utm_source' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'variant_b_subject' => 'nullable|string',
            'variant_b_html' => 'nullable|string',
            'test_percentage' => 'nullable|integer|min:5|max:50',
        ]);
        $data['status'] = 'draft';
        $data['utm_tags'] = $request->boolean('utm_tags');
        Broadcast::create($data);
        return redirect('/admin/broadcasts')->with('success', 'Broadcast created');
    }

    public function edit(string $id)
    {
        return view('admin.broadcasts.form', [
            'broadcast' => Broadcast::findOrFail($id),
            'templates' => Template::orderBy('name')->get(),
            'audiences' => Audience::withCount('contacts')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $broadcast = Broadcast::where('status', 'draft')->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'nullable|uuid',
            'audience_id' => 'required|uuid',
            'from_address' => 'required|email',
            'from_name' => 'nullable|string',
            'subject' => 'required|string',
            'html_body' => 'nullable|string',
            'utm_tags' => 'nullable|boolean',
            'utm_source' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'variant_b_subject' => 'nullable|string',
            'variant_b_html' => 'nullable|string',
            'test_percentage' => 'nullable|integer|min:5|max:50',
        ]);
        $data['utm_tags'] = $request->boolean('utm_tags');
        $broadcast->update($data);
        return redirect('/admin/broadcasts')->with('success', 'Broadcast updated');
    }

    public function send(string $id)
    {
        $broadcast = Broadcast::where('status', 'draft')->findOrFail($id);
        $audience = Audience::with('contacts')->findOrFail($broadcast->audience_id);
        $contacts = $audience->contacts()->where('unsubscribed', false)->where('confirmed', true)->get()
            ->filter(fn ($c) => !Suppression::isSuppressed($c->email));

        $broadcast->update(['status' => 'sending', 'total_recipients' => $contacts->count()]);

        $isABTest = !empty($broadcast->variant_b_subject);
        $testSize = $isABTest ? (int) ceil($contacts->count() * ($broadcast->test_percentage / 100)) : 0;

        $sent = 0; $failed = 0;
        foreach ($contacts as $i => $contact) {
            try {
                // A/B: first testSize get variant A or B randomly, rest get winner (or A by default)
                $useB = $isABTest && $i < $testSize && $i % 2 === 1;
                $subject = $useB ? $broadcast->variant_b_subject : $broadcast->subject;
                $html = $useB ? ($broadcast->variant_b_html ?? $broadcast->html_body) : $broadcast->html_body;

                $html = $this->replaceShortcodes($html ?? '', $contact);
                $subject = $this->replaceShortcodes($subject, $contact);

                // UTM tagging
                if ($broadcast->utm_tags && $html) {
                    $html = $this->tracking->addUtmParams($html, [
                        'source' => $broadcast->utm_source ?? $broadcast->name,
                        'medium' => $broadcast->utm_medium ?? 'email',
                        'campaign' => $broadcast->utm_campaign ?? $broadcast->name,
                    ]);
                }

                $this->mailService->send([
                    'from' => $broadcast->from_name ? "{$broadcast->from_name} <{$broadcast->from_address}>" : $broadcast->from_address,
                    'to' => $contact->email,
                    'subject' => $subject,
                    'html' => $html,
                ]);
                $sent++;
            } catch (\Exception $e) { $failed++; }
        }

        $broadcast->update(['status' => 'sent', 'sent_count' => $sent, 'failed_count' => $failed, 'sent_at' => now()]);
        return redirect('/admin/broadcasts')->with('success', "Broadcast sent to {$sent} recipients" . ($failed ? ", {$failed} failed" : ''));
    }

    public function destroy(string $id)
    {
        Broadcast::findOrFail($id)->delete();
        return redirect('/admin/broadcasts')->with('success', 'Broadcast deleted');
    }

    private function replaceShortcodes(string $content, $contact): string
    {
        return str_replace(
            ['{{contact.email}}', '{{contact.first_name}}', '{{contact.last_name}}', '{{contact.full_name}}', '{{date}}', '{{company}}'],
            [$contact->email, $contact->first_name ?? '', $contact->last_name ?? '', $contact->full_name, now()->format('F j, Y'), config('app.name')],
            $content
        );
    }
}
