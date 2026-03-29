@extends('layouts.admin')
@section('title', $replyTo ? 'Reply: ' . $replyTo->subject : 'Compose email')

@section('content')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <form method="POST" action="/admin/mail/send" class="card">
        @csrf
        @if($replyTo)<input type="hidden" name="reply_to_id" value="{{ $replyTo->id }}">@endif

        <div class="card__body" style="display:flex;flex-direction:column;gap:1rem;">
            <div class="form-group">
                <label class="form-label">From</label>
                <select name="from" class="form-select">
                    @forelse($mailboxes as $mb)
                        <option value="{{ $mb->name }} <{{ $mb->email }}>">{{ $mb->name }} &lt;{{ $mb->email }}&gt;</option>
                    @empty
                        <option value="CLOM <{{ config('mail.from.address') }}>">{{ config('mail.from.name') }} &lt;{{ config('mail.from.address') }}&gt;</option>
                    @endforelse
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="text" name="to" class="form-input" placeholder="email@example.com, ..." value="{{ $replyTo ? $replyTo->from_address : '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">CC</label>
                <input type="text" name="cc" class="form-input" placeholder="Optional">
            </div>
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input" value="{{ $replyTo ? 'Re: ' . $replyTo->subject : '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="body" id="email-body" class="form-textarea" style="min-height:16rem;font-family:inherit;" required>{{ $replyTo ? "\n\n\n---\nOn " . $replyTo->created_at->format('M d, Y H:i') . " " . $replyTo->from_address . " wrote:\n" . strip_tags($replyTo->html_body ?? $replyTo->text_body ?? '') : '' }}</textarea>
            </div>
        </div>
        <div class="dialog__footer">
            <button type="submit" class="btn btn--primary">Send email</button>
        </div>
    </form>

    <!-- AI Assistant -->
    <div class="card" style="position:sticky;top:5rem;align-self:start;">
        <div class="card__header">
            <span class="card__header-title" style="display:flex;align-items:center;gap:.5rem;">
                <svg style="width:1rem;height:1rem;color:var(--b500);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                AI Assistant
            </span>
        </div>
        <div class="card__body" style="display:flex;flex-direction:column;gap:.75rem;">
            <p class="text-sm" style="color:var(--text-tertiary);">Let AI help draft or reply to emails.</p>
            <button type="button" class="btn btn--secondary btn--sm" style="justify-content:flex-start;" onclick="aiQuickAction('Write a professional reply to this email')">Professional reply</button>
            <button type="button" class="btn btn--secondary btn--sm" style="justify-content:flex-start;" onclick="aiQuickAction('Write a short confirmation message')">Confirmation</button>
            <button type="button" class="btn btn--secondary btn--sm" style="justify-content:flex-start;" onclick="aiQuickAction('Write a polite decline')">Decline</button>
            <button type="button" class="btn btn--secondary btn--sm" style="justify-content:flex-start;" onclick="aiQuickAction('Suggest a follow-up question')">Follow-up</button>

            <hr style="border:none;border-top:1px solid var(--border);margin:.25rem 0;">

            <textarea id="ai-prompt" class="form-textarea" style="min-height:4rem;" placeholder="Describe what you want to write..."></textarea>
            <button type="button" class="btn btn--primary btn--sm" onclick="aiGenerate()" id="ai-generate-btn">Generate with AI</button>

            <div id="ai-result" style="display:none;">
                <hr style="border:none;border-top:1px solid var(--border);margin:.5rem 0;">
                <div id="ai-result-content" style="font-size:.8125rem;background:var(--n50);padding:.75rem;border-radius:.5rem;max-height:12rem;overflow-y:auto;"></div>
                <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                    <button type="button" class="btn btn--primary btn--sm" style="flex:1;" onclick="useAiResult()">Use this</button>
                    <button type="button" class="btn btn--secondary btn--sm" style="flex:1;" onclick="aiGenerate()">Regenerate</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let lastAiContent = '';
async function aiGenerate() {
    const prompt = document.getElementById('ai-prompt').value;
    if (!prompt.trim()) return;
    const btn = document.getElementById('ai-generate-btn');
    btn.textContent = 'Generating...'; btn.disabled = true;
    try {
        const res = await fetch('/admin/mail/ai-compose', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
            body: JSON.stringify({prompt, reply_to_id:'{{ $replyTo?->id ?? "" }}'})
        });
        const data = await res.json();
        lastAiContent = data.content || '';
        document.getElementById('ai-result-content').innerText = lastAiContent;
        document.getElementById('ai-result').style.display = 'block';
    } catch(e) { alert('AI error. Check your API key in AI Settings.'); }
    btn.textContent = 'Generate with AI'; btn.disabled = false;
}
function aiQuickAction(prompt) { document.getElementById('ai-prompt').value = prompt; aiGenerate(); }
function useAiResult() {
    const body = document.getElementById('email-body');
    const existing = body.value.trim();
    const quoteIdx = existing.indexOf('---\nOn ');
    body.value = quoteIdx > -1 ? lastAiContent + '\n\n' + existing.substring(quoteIdx) : lastAiContent + (existing ? '\n\n' + existing : '');
}
</script>
@endsection
