<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\Template;
use App\Models\Audience;
use App\Services\MailService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function __construct(private MailService $mailService) {}

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
        ]);
        $data['status'] = 'draft';
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
        ]);
        $broadcast->update($data);
        return redirect('/admin/broadcasts')->with('success', 'Broadcast updated');
    }

    public function send(string $id)
    {
        $broadcast = Broadcast::where('status', 'draft')->findOrFail($id);
        $audience = Audience::with('contacts')->findOrFail($broadcast->audience_id);
        $contacts = $audience->contacts()->where('unsubscribed', false)->get();

        $broadcast->update(['status' => 'sending', 'total_recipients' => $contacts->count()]);

        $sent = 0; $failed = 0;
        foreach ($contacts as $contact) {
            try {
                $html = $this->replaceShortcodes($broadcast->html_body ?? '', $contact);
                $this->mailService->send([
                    'from' => $broadcast->from_name ? "{$broadcast->from_name} <{$broadcast->from_address}>" : $broadcast->from_address,
                    'to' => $contact->email,
                    'subject' => $this->replaceShortcodes($broadcast->subject, $contact),
                    'html' => $html,
                ]);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
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
