@extends('layouts.admin')
@section('title', 'API Documentatie')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">API Documentatie</h2>
    <a href="/admin/docs/swagger" target="_blank" class="btn btn-primary btn--sm">Open Swagger UI &nearr;</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Sidebar nav -->
    <div class="lg:col-span-1">
        <ul class="menu bg-base-100 rounded-box shadow w-full">
            <li class="menu-title">Aan de slag</li>
            <li><a href="#authenticatie">Authenticatie</a></li>
            <li><a href="#rate-limiting">Rate Limiting</a></li>
            <li><a href="#fouten">Foutafhandeling</a></li>
            <li class="menu-title mt-2">Endpoints</li>
            <li><a href="#send-email">POST /emails</a></li>
            <li><a href="#send-batch">POST /emails/batch</a></li>
            <li><a href="#get-emails">GET /emails</a></li>
            <li><a href="#get-email">GET /emails/:id</a></li>
            <li><a href="#domains">Domeinen</a></li>
            <li><a href="#api-keys">API Keys</a></li>
            <li class="menu-title mt-2">SDK's</li>
            <li><a href="#sdk-curl">cURL</a></li>
            <li><a href="#sdk-php">PHP / Laravel</a></li>
            <li><a href="#sdk-node">Node.js</a></li>
            <li><a href="#sdk-python">Python</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="lg:col-span-3 space-y-8">
        <div class="bg-base-100 rounded-box shadow p-6">
            <p class="text-lg mb-4">CLOM biedt een <strong>Resend-compatible REST API</strong> voor het verzenden en ontvangen van e-mail. Als je al een Resend-integratie hebt, kun je met minimale aanpassingen overstappen.</p>
            <div class="alert alert-info"><span>Base URL: <code class="font-bold">http://{{ request()->getHost() }}/api</code></span></div>
        </div>

        <div id="authenticatie" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Authenticatie</h3>
            <p class="mb-3">Alle API-requests vereisen een Bearer token in de <code>Authorization</code> header:</p>
            <div class="mockup-code text-sm">
                <pre data-prefix="$"><code>curl -H "Authorization: Bearer clom_jouw_api_key" \</code></pre>
                <pre data-prefix=" "><code>     {{ request()->getSchemeAndHttpHost() }}/api/emails</code></pre>
            </div>
            <p class="mt-3 text-sm text-base-content/60">API keys maak je aan via <a href="/admin/api-keys" class="link link-primary">Beheer > API Keys</a> of via de API zelf.</p>
        </div>

        <div id="rate-limiting" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Rate Limiting</h3>
            <p>Standaard <strong>10 requests per seconde</strong> per API key. Bij overschrijding ontvang je een <code>429</code> response.</p>
        </div>

        <div id="fouten" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Foutafhandeling</h3>
            <p class="mb-3">Alle fouten retourneren een JSON-object:</p>
            <div class="mockup-code text-sm">
                <pre data-prefix=" "><code>{"statusCode": 401, "message": "Missing API key", "name": "missing_api_key"}</code></pre>
            </div>
            <table class="table table-sm mt-3">
                <thead><tr><th>Code</th><th>Betekenis</th></tr></thead>
                <tbody>
                    <tr><td><code>401</code></td><td>Ontbrekende API key</td></tr>
                    <tr><td><code>403</code></td><td>Ongeldige API key</td></tr>
                    <tr><td><code>404</code></td><td>Resource niet gevonden</td></tr>
                    <tr><td><code>422</code></td><td>Validatiefout</td></tr>
                    <tr><td><code>429</code></td><td>Rate limit bereikt</td></tr>
                </tbody>
            </table>
        </div>

        <div id="send-email" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-1">POST /emails</h3>
            <p class="text-sm text-base-content/60 mb-4">Verstuur een e-mail</p>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead><tr><th>Parameter</th><th>Type</th><th>Verplicht</th><th>Beschrijving</th></tr></thead>
                    <tbody>
                        <tr><td><code>from</code></td><td>string</td><td>Ja</td><td>Afzender, bijv. <code>"Naam &lt;email@domein.nl&gt;"</code></td></tr>
                        <tr><td><code>to</code></td><td>string | string[]</td><td>Ja</td><td>Ontvanger(s), max 50</td></tr>
                        <tr><td><code>subject</code></td><td>string</td><td>Ja</td><td>Onderwerp</td></tr>
                        <tr><td><code>html</code></td><td>string</td><td>Nee</td><td>HTML body</td></tr>
                        <tr><td><code>text</code></td><td>string</td><td>Nee</td><td>Platte tekst (auto-gegenereerd uit HTML)</td></tr>
                        <tr><td><code>cc</code></td><td>string | string[]</td><td>Nee</td><td>CC-ontvangers</td></tr>
                        <tr><td><code>bcc</code></td><td>string | string[]</td><td>Nee</td><td>BCC-ontvangers</td></tr>
                        <tr><td><code>reply_to</code></td><td>string | string[]</td><td>Nee</td><td>Reply-to adres(sen)</td></tr>
                        <tr><td><code>headers</code></td><td>object</td><td>Nee</td><td>Custom headers</td></tr>
                        <tr><td><code>tags</code></td><td>array</td><td>Nee</td><td>Metadata tags</td></tr>
                        <tr><td><code>scheduled_at</code></td><td>datetime</td><td>Nee</td><td>Gepland verzendtijdstip (ISO 8601)</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mockup-code text-sm mt-4">
                <pre data-prefix="$"><code>curl -X POST {{ request()->getSchemeAndHttpHost() }}/api/emails \</code></pre>
                <pre data-prefix=" "><code>  -H "Authorization: Bearer clom_xxx" \</code></pre>
                <pre data-prefix=" "><code>  -H "Content-Type: application/json" \</code></pre>
                <pre data-prefix=" "><code>  -d '{"from":"CLOM <info@code-labs.nl>","to":"klant@voorbeeld.nl","subject":"Test","html":"<h1>Hallo!</h1>"}'</code></pre>
            </div>
            <p class="mt-2 text-sm">Response: <code>{"id": "uuid-van-de-email"}</code></p>
        </div>

        <div id="sdk-curl" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Voorbeelden per taal</h3>

            <div role="tablist" class="tabs tabs-bordered">
                <input type="radio" name="sdk-tabs" role="tab" class="tab" aria-label="cURL" checked="checked" />
                <div role="tabpanel" class="tab-content py-4">
                    <div class="mockup-code text-sm">
                        <pre><code>curl -X POST {{ request()->getSchemeAndHttpHost() }}/api/emails \
  -H "Authorization: Bearer $CLOM_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "from": "App <noreply@code-labs.nl>",
    "to": "gebruiker@voorbeeld.nl",
    "subject": "Welkom!",
    "html": "&lt;h1&gt;Welkom bij onze dienst&lt;/h1&gt;"
  }'</code></pre>
                    </div>
                </div>

                <input type="radio" name="sdk-tabs" role="tab" class="tab" aria-label="PHP" id="sdk-php" />
                <div role="tabpanel" class="tab-content py-4">
                    <div class="mockup-code text-sm">
                        <pre><code>$response = Http::withToken($apiKey)
    ->post('{{ request()->getSchemeAndHttpHost() }}/api/emails', [
        'from' => 'App <noreply@code-labs.nl>',
        'to'   => 'gebruiker@voorbeeld.nl',
        'subject' => 'Welkom!',
        'html' => '<h1>Welkom!</h1>',
    ]);

$emailId = $response->json('id');</code></pre>
                    </div>
                </div>

                <input type="radio" name="sdk-tabs" role="tab" class="tab" aria-label="Node.js" id="sdk-node" />
                <div role="tabpanel" class="tab-content py-4">
                    <div class="mockup-code text-sm">
                        <pre><code>const res = await fetch('{{ request()->getSchemeAndHttpHost() }}/api/emails', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    from: 'App <noreply@code-labs.nl>',
    to: 'gebruiker@voorbeeld.nl',
    subject: 'Welkom!',
    html: '<h1>Welkom!</h1>',
  }),
});
const { id } = await res.json();</code></pre>
                    </div>
                </div>

                <input type="radio" name="sdk-tabs" role="tab" class="tab" aria-label="Python" id="sdk-python" />
                <div role="tabpanel" class="tab-content py-4">
                    <div class="mockup-code text-sm">
                        <pre><code>import requests

response = requests.post(
    '{{ request()->getSchemeAndHttpHost() }}/api/emails',
    headers={'Authorization': f'Bearer {api_key}'},
    json={
        'from': 'App <noreply@code-labs.nl>',
        'to': 'gebruiker@voorbeeld.nl',
        'subject': 'Welkom!',
        'html': '<h1>Welkom!</h1>',
    }
)
email_id = response.json()['id']</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
