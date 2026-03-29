@extends('layouts.admin')
@section('title', 'AI Settings')
@section('subtitle', 'Configure your AI provider for smart email features')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

    {{-- Provider Configuration --}}
    <div class="card">
        <div class="card__header">
            <span class="card__header-title">Provider Configuration (BYOK)</span>
        </div>
        <div class="card__body">
            <form method="POST" action="/admin/ai-settings">
                @csrf

                <div class="form-group">
                    <label class="form-label">AI Provider</label>
                    <select name="provider" id="ai-provider" class="form-select" onchange="updateModels()">
                        @foreach($providers as $key => $provider)
                            <option value="{{ $key }}" {{ ($setting?->provider ?? 'anthropic') === $key ? 'selected' : '' }}>{{ $provider['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Model</label>
                    <select name="model" id="ai-model" class="form-select">
                        @foreach($providers[$setting?->provider ?? 'anthropic']['models'] ?? [] as $model)
                            <option value="{{ $model }}" {{ ($setting?->model ?? '') === $model ? 'selected' : '' }}>{{ $model }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <input type="password" name="api_key" class="form-input" placeholder="{{ $setting?->api_key ? '********' : 'sk-ant-..., sk-..., AIza...' }}" value="">
                    <p class="form-hint">Leave empty to keep the current key. No key needed for Ollama.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Base URL (optional)</label>
                    <input type="url" name="base_url" class="form-input" placeholder="http://localhost:11434 (for Ollama)" value="{{ $setting?->base_url }}">
                    <p class="form-hint">Only required for Ollama or custom endpoints.</p>
                </div>

                <div style="display:flex;gap:.5rem;margin-top:1.25rem;">
                    <button type="submit" class="btn btn--primary">Save</button>
                    <button type="button" class="btn btn--secondary" onclick="testAi()">Test Connection</button>
                </div>
            </form>

            <div id="test-result" style="display:none;margin-top:1rem;"></div>
        </div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Supported Providers --}}
        <div class="card">
            <div class="card__header">
                <span class="card__header-title">Supported Providers</span>
            </div>
            <div class="card__body" style="padding:0;">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Models</th>
                            <th>Free?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tbl__text-primary">Anthropic (Claude)</td>
                            <td class="text-sm">claude-sonnet-4, claude-haiku-4.5, claude-opus-4</td>
                            <td><span class="badge badge--warning">Paid</span></td>
                        </tr>
                        <tr>
                            <td class="tbl__text-primary">OpenAI (GPT)</td>
                            <td class="text-sm">gpt-4o, gpt-4o-mini, o3-mini</td>
                            <td><span class="badge badge--warning">Paid</span></td>
                        </tr>
                        <tr>
                            <td class="tbl__text-primary">Google Gemini</td>
                            <td class="text-sm">gemini-2.5-pro, gemini-2.0-flash</td>
                            <td><span class="badge badge--success">Free tier</span></td>
                        </tr>
                        <tr>
                            <td class="tbl__text-primary">Ollama (Local)</td>
                            <td class="text-sm">llama3.1, mistral, mixtral, ...</td>
                            <td><span class="badge badge--success">Free</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Status --}}
        <div class="card">
            <div class="card__header">
                <span class="card__header-title">Status</span>
            </div>
            <div class="card__body">
                @if($setting && $setting->api_key)
                    <div class="alert alert--success" style="margin-bottom:0;">
                        AI is configured: <strong>{{ $providers[$setting->provider]['name'] ?? $setting->provider }}</strong> ({{ $setting->model }})
                    </div>
                @else
                    <div class="alert alert--warning" style="margin-bottom:0;">
                        No AI provider configured. Set an API key or connect to Ollama.
                    </div>
                @endif
            </div>
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
    box.style.display = 'block';
    box.innerHTML = '<div class="alert alert--info" style="margin-bottom:0;">Testing connection...</div>';

    try {
        const res = await fetch('/admin/ai-settings/test', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            },
        });
        const data = await res.json();
        box.innerHTML = data.success
            ? `<div class="alert alert--success" style="margin-bottom:0;">${data.response} (${data.provider}/${data.model})</div>`
            : `<div class="alert alert--danger" style="margin-bottom:0;">Error: ${data.response}</div>`;
    } catch(e) {
        box.innerHTML = '<div class="alert alert--danger" style="margin-bottom:0;">Connection failed</div>';
    }
}
</script>
@endsection
