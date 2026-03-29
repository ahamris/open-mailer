@extends('layouts.admin')
@section('title', $replyTo ? 'Beantwoord: ' . $replyTo->subject : 'Nieuwe mail')

@section('content')
<div class="mb-4">
    <a href="/admin/mail" class="btn btn-ghost btn-sm">&larr; Terug</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Compose form -->
    <div class="lg:col-span-2">
        <form method="POST" action="/admin/mail/send" class="bg-base-100 rounded-box shadow">
            @csrf
            @if($replyTo)
                <input type="hidden" name="reply_to_id" value="{{ $replyTo->id }}">
            @endif

            <div class="p-5 space-y-3">
                <h2 class="text-xl font-bold mb-4">{{ $replyTo ? 'Beantwoord' : 'Nieuwe e-mail' }}</h2>

                <fieldset class="fieldset">
                    <label class="fieldset-label">Van</label>
                    <select name="from" class="select select-bordered w-full">
                        @forelse($mailboxes as $mb)
                            <option value="{{ $mb->name }} <{{ $mb->email }}>">{{ $mb->name }} &lt;{{ $mb->email }}&gt;</option>
                        @empty
                            <option value="CLOM <{{ config('mail.from.address') }}>">{{ config('mail.from.name') }} &lt;{{ config('mail.from.address') }}&gt;</option>
                        @endforelse
                    </select>
                </fieldset>

                <fieldset class="fieldset">
                    <label class="fieldset-label">Aan</label>
                    <input type="text" name="to" class="input input-bordered w-full" placeholder="email@voorbeeld.nl, ..." value="{{ $replyTo ? $replyTo->from_address : '' }}" required>
                </fieldset>

                <fieldset class="fieldset">
                    <label class="fieldset-label">CC</label>
                    <input type="text" name="cc" class="input input-bordered w-full" placeholder="optioneel">
                </fieldset>

                <fieldset class="fieldset">
                    <label class="fieldset-label">Onderwerp</label>
                    <input type="text" name="subject" class="input input-bordered w-full" value="{{ $replyTo ? 'Re: ' . $replyTo->subject : '' }}" required>
                </fieldset>

                <fieldset class="fieldset">
                    <label class="fieldset-label">Bericht</label>
                    <textarea name="body" id="email-body" class="textarea textarea-bordered w-full h-64 font-mono text-sm" required>{{ $replyTo ? "\n\n\n---\nOp " . $replyTo->created_at->format('d M Y H:i') . " schreef " . $replyTo->from_address . ":\n" . strip_tags($replyTo->html_body ?? $replyTo->text_body ?? '') : '' }}</textarea>
                </fieldset>
            </div>

            <div class="p-5 border-t border-base-200 flex justify-between">
                <button type="submit" class="btn btn-primary">Verstuur</button>
                <button type="button" class="btn btn-ghost" onclick="document.querySelector('[name=body]').value = ''">Wis</button>
            </div>
        </form>
    </div>

    <!-- AI Assistant panel -->
    <div class="lg:col-span-1">
        <div class="bg-base-100 rounded-box shadow sticky top-6">
            <div class="p-4 border-b border-base-200">
                <h3 class="font-semibold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    AI Assistent
                </h3>
            </div>
            <div class="p-4 space-y-3">
                <p class="text-sm text-base-content/60">Laat AI een concept schrijven of helpen met het antwoord.</p>

                <div class="space-y-2">
                    <button class="btn btn-soft btn-sm w-full justify-start" onclick="aiQuickAction('Schrijf een professioneel antwoord op deze e-mail')">Professioneel antwoord</button>
                    <button class="btn btn-soft btn-sm w-full justify-start" onclick="aiQuickAction('Schrijf een vriendelijk maar kort bevestigingsbericht')">Bevestiging</button>
                    <button class="btn btn-soft btn-sm w-full justify-start" onclick="aiQuickAction('Schrijf een beleefde afwijzing')">Afwijzing</button>
                    <button class="btn btn-soft btn-sm w-full justify-start" onclick="aiQuickAction('Stel een follow-up vraag voor')">Follow-up</button>
                </div>

                <div class="divider text-xs">of beschrijf zelf</div>

                <textarea id="ai-prompt" class="textarea textarea-bordered w-full h-24 text-sm" placeholder="Bijv: Schrijf een offerte voor een OPMS implementatie bij gemeente X..."></textarea>

                <button class="btn btn-primary btn-sm w-full" onclick="aiGenerate()" id="ai-generate-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Genereer met AI
                </button>

                <div id="ai-result" class="hidden">
                    <div class="divider text-xs">AI Resultaat</div>
                    <div id="ai-result-content" class="text-sm bg-base-200 p-3 rounded-box max-h-64 overflow-y-auto"></div>
                    <div class="flex gap-2 mt-2">
                        <button class="btn btn-primary btn-xs flex-1" onclick="useAiResult()">Gebruik dit</button>
                        <button class="btn btn-ghost btn-xs flex-1" onclick="aiGenerate()">Opnieuw</button>
                    </div>
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
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Genereren...';
    btn.disabled = true;

    try {
        const res = await fetch('/admin/mail/ai-compose', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                prompt: prompt,
                reply_to_id: '{{ $replyTo?->id ?? "" }}'
            })
        });
        const data = await res.json();
        lastAiContent = data.content || '';
        document.getElementById('ai-result-content').innerText = lastAiContent;
        document.getElementById('ai-result').classList.remove('hidden');
    } catch(e) {
        alert('AI fout. Controleer de Anthropic API key in config/services.php');
    }

    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Genereer met AI';
    btn.disabled = false;
}

function aiQuickAction(prompt) {
    document.getElementById('ai-prompt').value = prompt;
    aiGenerate();
}

function useAiResult() {
    const body = document.getElementById('email-body');
    const existing = body.value.trim();
    // If replying, insert before the quote
    const quoteIdx = existing.indexOf('---\nOp ');
    if (quoteIdx > -1) {
        body.value = lastAiContent + '\n\n' + existing.substring(quoteIdx);
    } else {
        body.value = lastAiContent + (existing ? '\n\n' + existing : '');
    }
}
</script>
@endsection
