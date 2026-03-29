<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use App\Models\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct(private MailService $mailService) {}

    // POST /emails
    public function send(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|string',
            'to' => 'required',
            'subject' => 'required|string',
            'html' => 'nullable|string',
            'text' => 'nullable|string',
            'cc' => 'nullable',
            'bcc' => 'nullable',
            'reply_to' => 'nullable',
            'headers' => 'nullable|array',
            'tags' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'idempotency_key' => 'nullable|string|max:256',
        ]);

        $email = $this->mailService->send($validated, $request->attributes->get("api_key")->id);

        return response()->json(['id' => $email->id], 200);
    }

    // POST /emails/batch
    public function sendBatch(Request $request)
    {
        $validated = $request->validate([
            '*.from' => 'required|string',
            '*.to' => 'required',
            '*.subject' => 'required|string',
        ]);

        $emails = $this->mailService->sendBatch($request->all(), $request->attributes->get("api_key")->id);

        return response()->json([
            'data' => collect($emails)->map(fn ($e) => ['id' => $e->id]),
        ]);
    }

    // GET /emails
    public function index(Request $request)
    {
        $emails = Email::where('direction', 'outbound')
            ->orderByDesc('created_at')
            ->cursorPaginate(20);

        return response()->json($emails);
    }

    // GET /emails/{id}
    public function show(string $id)
    {
        $email = Email::findOrFail($id);

        return response()->json([
            'id' => $email->id,
            'from' => $email->from_name ? "{$email->from_name} <{$email->from_address}>" : $email->from_address,
            'to' => $email->to_addresses,
            'cc' => $email->cc_addresses,
            'bcc' => $email->bcc_addresses,
            'reply_to' => $email->reply_to,
            'subject' => $email->subject,
            'html' => $email->html_body,
            'text' => $email->text_body,
            'status' => $email->status,
            'tags' => $email->tags,
            'headers' => $email->headers,
            'created_at' => $email->created_at->toISOString(),
            'sent_at' => $email->sent_at?->toISOString(),
        ]);
    }

    // PATCH /emails/{id}
    public function update(Request $request, string $id)
    {
        $email = Email::where('status', 'scheduled')->findOrFail($id);
        $email->update($request->only(['scheduled_at']));
        return response()->json(['id' => $email->id]);
    }

    // DELETE /emails/{id}
    public function destroy(string $id)
    {
        $email = Email::where('status', 'scheduled')->findOrFail($id);
        $email->update(['status' => 'cancelled']);
        return response()->json(['id' => $email->id, 'deleted' => true]);
    }
}
