<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\Mailbox;
use App\Services\AiService;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MailClientController extends Controller
{
    public function __construct(
        private MailService $mailService,
        private AiService $aiService,
    ) {}

    public function inbox(Request $request)
    {
        $folder = $request->get('folder', 'inbox');
        $query = Email::orderByDesc('created_at');

        if ($folder === 'inbox') {
            $query->where('direction', 'inbound')->where('folder', 'inbox');
        } elseif ($folder === 'sent') {
            $query->where('direction', 'outbound');
        } elseif ($folder === 'starred') {
            $query->where('is_starred', true);
        } elseif ($folder === 'drafts') {
            $query->where('status', 'draft');
        } else {
            $query->where('folder', $folder);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('from_address', 'like', "%{$search}%")
                  ->orWhere('text_body', 'like', "%{$search}%");
            });
        }

        return view('admin.mail.inbox', [
            'emails' => $query->paginate(25),
            'folder' => $folder,
            'unreadCount' => Email::where('direction', 'inbound')->where('is_read', false)->count(),
        ]);
    }

    public function show(string $id)
    {
        $email = Email::findOrFail($id);
        if (!$email->is_read) {
            $email->update(['is_read' => true]);
        }
        $thread = $email->thread_id
            ? Email::where('thread_id', $email->thread_id)->orderBy('created_at')->get()
            : collect([$email]);

        return view('admin.mail.show', compact('email', 'thread'));
    }

    public function compose(Request $request)
    {
        $replyTo = $request->reply_to ? Email::find($request->reply_to) : null;
        $mailboxes = Mailbox::where('active', true)->get();
        return view('admin.mail.compose', compact('replyTo', 'mailboxes'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|string',
            'to' => 'required|string',
            'cc' => 'nullable|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'reply_to_id' => 'nullable|uuid',
        ]);

        $toAddresses = array_map('trim', explode(',', $validated['to']));
        $ccAddresses = !empty($validated['cc']) ? array_map('trim', explode(',', $validated['cc'])) : null;

        $parentEmail = $validated['reply_to_id'] ? Email::find($validated['reply_to_id']) : null;
        $threadId = $parentEmail?->thread_id ?? ($parentEmail ? $parentEmail->id : Str::uuid()->toString());

        $email = $this->mailService->send([
            'from' => $validated['from'],
            'to' => $toAddresses,
            'cc' => $ccAddresses,
            'subject' => $validated['subject'],
            'html' => $validated['body'],
        ]);

        $email->update([
            'thread_id' => $threadId,
            'parent_id' => $parentEmail?->id,
            'folder' => 'sent',
        ]);

        return redirect('/admin/mail')->with('success', 'E-mail verstuurd!');
    }

    public function toggleStar(string $id)
    {
        $email = Email::findOrFail($id);
        $email->update(['is_starred' => !$email->is_starred]);
        return back();
    }

    public function moveToTrash(string $id)
    {
        Email::findOrFail($id)->update(['folder' => 'trash']);
        return back()->with('success', 'Verplaatst naar prullenbak');
    }

    public function aiCompose(Request $request)
    {
        $request->validate(['prompt' => 'required|string', 'reply_to_id' => 'nullable|uuid']);

        $replyTo = $request->reply_to_id ? Email::find($request->reply_to_id) : null;
        $result = $this->aiService->compose($request->prompt, $replyTo);

        if ($replyTo) {
            $this->aiService->saveConversation($replyTo, 'reply', $request->prompt, $result);
        }

        return response()->json([
            'content' => $result['content'],
            'tokens' => $result['input_tokens'] + $result['output_tokens'],
        ]);
    }

    public function aiSummarize(string $id)
    {
        $email = Email::findOrFail($id);
        $result = $this->aiService->summarize($email);
        $this->aiService->saveConversation($email, 'summarize', 'Vat samen', $result);
        return response()->json(['summary' => $result['content']]);
    }
}
