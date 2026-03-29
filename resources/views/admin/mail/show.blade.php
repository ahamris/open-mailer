@extends('layouts.admin')
@section('title', $email->subject)

@section('content')
<div class="mb-4">
    <a href="/admin/mail" class="btn btn-ghost btn-sm">&larr; Terug</a>
</div>

<div class="bg-base-100 rounded-box shadow">
    <!-- Header -->
    <div class="p-5 border-b border-base-200">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold">{{ $email->subject }}</h2>
                <div class="flex items-center gap-2 mt-2 text-sm text-base-content/70">
                    <span class="badge badge-{{ $email->direction === 'inbound' ? 'info' : 'ghost' }} badge-sm">{{ $email->direction === 'inbound' ? 'Ontvangen' : 'Verzonden' }}</span>
                    <span class="badge badge-{{ in_array($email->status, ['sent','delivered','received']) ? 'success' : ($email->status === 'failed' ? 'error' : 'warning') }} badge-sm">{{ $email->status }}</span>
                    @if($email->spf_result)<span class="badge badge-{{ $email->spf_result === 'pass' ? 'success' : 'error' }} badge-xs">SPF: {{ $email->spf_result }}</span>@endif
                    @if($email->dkim_result)<span class="badge badge-{{ $email->dkim_result === 'pass' ? 'success' : 'error' }} badge-xs">DKIM: {{ $email->dkim_result }}</span>@endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="/admin/mail/compose?reply_to={{ $email->id }}" class="btn btn-primary btn-sm">Beantwoord</a>
                <form method="POST" action="/admin/mail/{{ $email->id }}/star">@csrf
                    <button class="btn btn-ghost btn-sm">{{ $email->is_starred ? 'Ster verwijderen' : 'Ster toevoegen' }}</button>
                </form>
                <button class="btn btn-ghost btn-sm" onclick="aiSummarize('{{ $email->id }}')">AI Samenvatting</button>
                <form method="POST" action="/admin/mail/{{ $email->id }}/trash">@csrf
                    <button class="btn btn-ghost btn-sm text-error">Verwijder</button>
                </form>
            </div>
        </div>
    </div>

    <!-- AI Summary box (hidden by default) -->
    <div id="ai-summary" class="hidden p-4 bg-info/10 border-b border-base-200">
        <div class="flex items-start gap-2">
            <span class="badge badge-info badge-sm mt-0.5">AI</span>
            <div id="ai-summary-content" class="text-sm prose prose-sm max-w-none"></div>
        </div>
    </div>

    <!-- Email meta -->
    <div class="p-5 border-b border-base-200 text-sm space-y-1">
        <div><strong>Van:</strong> {{ $email->from_name ? "{$email->from_name} <{$email->from_address}>" : $email->from_address }}</div>
        <div><strong>Aan:</strong> {{ is_array($email->to_addresses) ? implode(', ', $email->to_addresses) : $email->to_addresses }}</div>
        @if($email->cc_addresses)<div><strong>CC:</strong> {{ implode(', ', $email->cc_addresses) }}</div>@endif
        <div><strong>Datum:</strong> {{ $email->created_at->format('d M Y H:i:s') }}</div>
        @if($email->message_id)<div class="text-xs text-base-content/40"><strong>Message-ID:</strong> {{ $email->message_id }}</div>@endif
    </div>

    <!-- Body -->
    <div class="p-5">
        @if($email->html_body)
            <div class="prose max-w-none"><iframe srcdoc="{{ e($email->html_body) }}" sandbox="" class="w-full min-h-96 border-0 bg-white rounded" onload="this.style.height = this.contentDocument.body.scrollHeight + 'px'"></iframe></div>
        @elseif($email->text_body)
            <pre class="whitespace-pre-wrap text-sm">{{ $email->text_body }}</pre>
        @else
            <p class="text-base-content/40 italic">Geen inhoud</p>
        @endif
    </div>

    <!-- Thread -->
    @if($thread->count() > 1)
    <div class="border-t border-base-200 p-5">
        <h3 class="font-semibold mb-3">Thread ({{ $thread->count() }} berichten)</h3>
        <div class="space-y-3">
            @foreach($thread as $msg)
                @if($msg->id !== $email->id)
                <div class="collapse collapse-arrow bg-base-200 rounded-box">
                    <input type="checkbox" />
                    <div class="collapse-title text-sm">
                        <span class="font-medium">{{ $msg->from_name ?? $msg->from_address }}</span>
                        <span class="text-base-content/50">— {{ $msg->created_at->format('d M H:i') }}</span>
                    </div>
                    <div class="collapse-content">
                        <div class="prose prose-sm max-w-none"><div class="text-sm">{{ Str::limit(strip_tags($msg->html_body ?? $msg->text_body ?? '')) !!}</div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
async function aiSummarize(emailId) {
    const box = document.getElementById('ai-summary');
    const content = document.getElementById('ai-summary-content');
    box.classList.remove('hidden');
    content.innerHTML = '<span class="loading loading-dots loading-sm"></span> AI aan het analyseren...';

    try {
        const res = await fetch(`/admin/mail/${emailId}/ai-summarize`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}
        });
        const data = await res.json();
        content.innerHTML = data.summary ? data.summary.replace(/\n/g, '<br>') : 'Geen samenvatting beschikbaar.';
    } catch(e) {
        content.innerHTML = '<span class="text-error">Fout bij AI samenvatting. Controleer je Anthropic API key.</span>';
    }
}
</script>
@endsection
