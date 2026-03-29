@extends('layouts.admin')
@section('title', 'AI Instellingen')

@section('content')
<h2 class="text-2xl font-bold mb-6">AI Instellingen</h2>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <form method="POST" action="/admin/ai-settings" class="bg-base-100 rounded-box shadow p-6">
        @csrf
        <h3 class="font-semibold text-lg mb-4">Provider configuratie (BYOK)</h3>

        <fieldset class="fieldset">
            <label class="fieldset-label">AI Provider</label>
            <select name="provider" id="ai-provider" class="select select-bordered w-full" onchange="updateModels()">
                @foreach($providers as $key => $provider)
                    <option value="{{ $key }}" {{ ($setting?->provider ?? 'anthropic') === $key ? 'selected' : '' }}>{{ $provider['name'] }}</option>
                @endforeach
            </select>
        </fieldset>

        <fieldset class="fieldset mt-3">
            <label class="fieldset-label">Model</label>
            <select name="model" id="ai-model" class="select select-bordered w-full">
                @foreach($providers[$setting?->provider ?? 'anthropic']['models'] ?? [] as $model)
                    <option value="{{ $model }}" {{ ($setting?->model ?? '') === $model ? 'selected' : '' }}>{{ $model }}</option>
                @endforeach
            </select>
        </fieldset>

        <fieldset class="fieldset mt-3">
            <label class="fieldset-label">API Key</label>
            <input type="password" name="api_key" class="input input-bordered w-full" placeholder="{{ $setting?->api_key ? '********' : 'sk-ant-..., sk-..., AIza...' }}" value="">
            <p class="text-xs text-base-content/50 mt-1">Laat leeg om de huidige key te behouden. Voor Ollama is geen key nodig.</p>
        </fieldset>

        <fieldset class="fieldset mt-3">
            <label class="fieldset-label">Base URL (optioneel)</label>
            <input type="url" name="base_url" class="input input-bordered w-full" placeholder="http://localhost:11434 (voor Ollama)" value="{{ $setting?->base_url }}">
            <p class="text-xs text-base-content/50 mt-1">Alleen nodig voor Ollama of custom endpoints.</p>
        </fieldset>

        <div class="flex gap-2 mt-4">
            <button type="submit" class="btn btn--primary">Save</button>
            <button type="button" class="btn btn--ghost" onclick="testAi()">Test verbinding</button>
        </div>

        <div id="test-result" class="hidden mt-4"></div>
    </form>

    <div class="space-y-4">
        <div class="bg-base-100 rounded-box shadow p-6">
            <h3 class="font-semibold text-lg mb-3">Ondersteunde providers</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead><tr><th>Provider</th><th>Modellen</th><th>Gratis?</th></tr></thead>
                    <tbody>
                        <tr><td>Anthropic (Claude)</td><td>claude-sonnet-4, claude-haiku-4.5, claude-opus-4</td><td><span class="badge badge-warning badge-xs">Betaald</span></td></tr>
                        <tr><td>OpenAI (GPT)</td><td>gpt-4o, gpt-4o-mini, o3-mini</td><td><span class="badge badge-warning badge-xs">Betaald</span></td></tr>
                        <tr><td>Google Gemini</td><td>gemini-2.5-pro, gemini-2.0-flash</td><td><span class="badge badge-success badge-xs">Free tier</span></td></tr>
                        <tr><td>Ollama (Lokaal)</td><td>llama3.1, mistral, mixtral, ...</td><td><span class="badge badge-success badge-xs">Gratis</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-base-100 rounded-box shadow p-6">
            <h3 class="font-semibold text-lg mb-3">Status</h3>
            @if($setting && $setting->api_key)
                <div class="alert alert-success"><span>AI is geconfigureerd: <strong>{{ $providers[$setting->provider]['name'] ?? $setting->provider }}</strong> ({{ $setting->model }})</span></div>
            @else
                <div class="alert alert-warning"><span>Geen AI provider geconfigureerd. Stel een API key in of verbind met Ollama.</span></div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const providers = @json($providers);

function updateModels() {
    const provider = document.getElementById('ai-provider').value;
    const modelSelect = document.getElementById('ai-model');
    modelSelect.innerHTML = '';
    (providers[provider]?.models || []).forEach(m => {
        const opt = document.createElement('option');
        opt.value = m; opt.text = m;
        modelSelect.add(opt);
    });
}

async function testAi() {
    const box = document.getElementById('test-result');
    box.classList.remove('hidden');
    box.innerHTML = '<div class="alert"><span class="loading loading-spinner loading-sm"></span> Testen...</div>';

    try {
        const res = await fetch('/admin/ai-settings/test', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'},
        });
        const data = await res.json();
        box.innerHTML = data.success
            ? `<div class="alert alert-success"><span>${data.response} (${data.provider}/${data.model})</span></div>`
            : `<div class="alert alert-error"><span>Fout: ${data.response}</span></div>`;
    } catch(e) {
        box.innerHTML = '<div class="alert alert-error"><span>Verbinding mislukt</span></div>';
    }
}
</script>
@endsection
