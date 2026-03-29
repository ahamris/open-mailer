@extends('layouts.admin')
@section('title', $email->subject)

@section('actions')
<a href="/admin/mail/compose?reply_to={{ $email->id }}" class="btn btn--primary btn--sm">Reply</a>
<form method="POST" action="/admin/mail/{{ $email->id }}/star" style="margin:0;">@csrf
    <button class="btn btn--secondary btn--sm">{{ $email->is_starred ? 'Remove star' : 'Star' }}</button>
</form>
<button class="btn btn--secondary btn--sm" onclick="aiSummarize('{{ $email->id }}')">AI Summary</button>
<form method="POST" action="/admin/mail/{{ $email->id }}/trash" style="margin:0;">@csrf
    <button class="btn btn--ghost-danger btn--sm">Delete</button>
</form>
@endsection

@section('content')
<a href="/admin/mail" style="display:inline-flex;align-items:center;gap:.25rem;font-size:.8125rem;color:var(--text-tertiary);text-decoration:none;margin-bottom:1rem;">&larr; Back to inbox</a>

<!-- AI Summary -->
<div id="ai-summary" style="display:none;" class="alert alert--info" role="alert">
    <strong>AI:</strong> <span id="ai-summary-content"></span>
</div>

<div class="card">
    <!-- Meta -->
    <div class="card__body" style="border-bottom:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            @switch($email->status)
                @case('sent') @case('delivered') @case('received')
                    <span class="badge badge--success"><span class="dot"></span>{{ ucfirst($email->status) }}</span>@break
                @case('failed') @case('bounced')
                    <span class="badge badge--danger"><span class="dot"></span>{{ ucfirst($email->status) }}</span>@break
                @default
                    <span class="badge badge--neutral"><span class="dot"></span>{{ ucfirst($email->status) }}</span>
            @endswitch
            @if($email->spf_result)<span class="badge badge--{{ $email->spf_result === 'pass' ? 'success' : 'danger' }}">SPF: {{ $email->spf_result }}</span>@endif
            @if($email->dkim_result)<span class="badge badge--{{ $email->dkim_result === 'pass' ? 'success' : 'danger' }}">DKIM: {{ $email->dkim_result }}</span>@endif
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.375rem;font-size:.8125rem;color:var(--text-secondary);">
            <div><strong>From:</strong> {{ $email->from_name ? "{$email->from_name} <{$email->from_address}>" : $email->from_address }}</div>
            <div><strong>To:</strong> {{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</div>
            @if($email->cc_addresses)<div><strong>CC:</strong> {{ implode(', ', $email->cc_addresses) }}</div>@endif
            <div><strong>Date:</strong> {{ $email->created_at->format('M d, Y H:i:s') }}</div>
        </div>
    </div>

    <!-- Body -->
    <div class="card__body">
        @if($email->html_body)
            <iframe srcdoc="{{ e($email->html_body) }}" sandbox="" style="width:100%;min-height:24rem;border:none;background:white;" onload="this.style.height=this.contentDocument.body.scrollHeight+'px'"></iframe>
        @elseif($email->text_body)
            <pre style="white-space:pre-wrap;font-size:.875rem;">{{ $email->text_body }}</pre>
        @else
            <p style="color:var(--n400);font-style:italic;">No content</p>
        @endif
    </div>

    <!-- Thread -->
    @if($thread->count() > 1)
    <div class="card__body" style="border-top:1px solid var(--border);">
        <div class="card__header-title" style="margin-bottom:.75rem;">Thread ({{ $thread->count() }} messages)</div>
        @foreach($thread as $msg)
            @if($msg->id !== $email->id)
            <details style="background:var(--n50);border-radius:.5rem;margin-bottom:.5rem;">
                <summary style="padding:.75rem;font-size:.8125rem;cursor:pointer;">
                    <strong>{{ $msg->from_name ?? $msg->from_address }}</strong>
                    <span style="color:var(--text-tertiary);"> — {{ $msg->created_at->format('M d, H:i') }}</span>
                </summary>
                <div style="padding:0 .75rem .75rem;font-size:.875rem;">{{ strip_tags($msg->html_body ?? $msg->text_body ?? '') }}</div>
            </details>
            @endif
        @endforeach
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
async function aiSummarize(id) {
    const box = document.getElementById('ai-summary');
    const content = document.getElementById('ai-summary-content');
    box.style.display = 'flex';
    content.textContent = 'Analyzing...';
    try {
        const res = await fetch(`/admin/mail/${id}/ai-summarize`, {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}});
        const data = await res.json();
        content.textContent = data.summary || 'No summary available.';
    } catch(e) { content.textContent = 'Error. Check your AI settings.'; }
}
</script>
@endsection
