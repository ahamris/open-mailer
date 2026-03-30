@extends('layouts.admin')
@section('title', 'API Reference')
@section('subtitle', 'Resend-compatible REST API for sending and receiving email')

@section('actions')
<a href="/admin/docs/swagger" target="_blank" class="btn btn--primary btn--sm">Open Swagger UI &nearr;</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:14rem 1fr;gap:1.5rem;align-items:start;">

    {{-- Sidebar Navigation --}}
    <div class="card" style="position:sticky;top:5rem;">
        <div class="card__body" style="padding:.75rem;">
            <p class="text-xs font-semibold" style="color:var(--text-tertiary);text-transform:uppercase;margin-bottom:.5rem;">Getting Started</p>
            <nav style="display:flex;flex-direction:column;gap:.125rem;">
                <a href="#authentication" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Authentication</a>
                <a href="#rate-limiting" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Rate Limiting</a>
                <a href="#error-handling" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Error Handling</a>
            </nav>
            <p class="text-xs font-semibold" style="color:var(--text-tertiary);text-transform:uppercase;margin:.75rem 0 .5rem;">Endpoints</p>
            <nav style="display:flex;flex-direction:column;gap:.125rem;">
                <a href="#send-email" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">POST /emails</a>
                <a href="#send-batch" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">POST /emails/batch</a>
                <a href="#get-emails" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">GET /emails</a>
                <a href="#get-email" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">GET /emails/:id</a>
                <a href="#domains-api" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Domains</a>
                <a href="#api-keys-api" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">API Keys</a>
            </nav>
            <p class="text-xs font-semibold" style="color:var(--text-tertiary);text-transform:uppercase;margin:.75rem 0 .5rem;">SDKs</p>
            <nav style="display:flex;flex-direction:column;gap:.125rem;">
                <a href="#sdk-curl" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">cURL</a>
                <a href="#sdk-php" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">PHP / Laravel</a>
                <a href="#sdk-node" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Node.js</a>
                <a href="#sdk-python" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Python</a>
            </nav>
        </div>
    </div>

    {{-- Content --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Intro --}}
        <div class="card">
            <div class="card__body">
                <p style="font-size:1rem;margin-bottom:.75rem;">CLOM provides a <strong>Resend-compatible REST API</strong> for sending and receiving email. If you already have a Resend integration, you can switch with minimal changes.</p>
                <div class="alert alert--info" style="margin-bottom:0;">
                    Base URL: <code class="font-semibold">{{ request()->getSchemeAndHttpHost() }}/api</code>
                </div>
            </div>
        </div>

        {{-- Authentication --}}
        <div id="authentication" class="card">
            <div class="card__header">
                <span class="card__header-title">Authentication</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">All API requests require a Bearer token in the <code>Authorization</code> header:</p>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;">
                    curl -H "Authorization: Bearer clom_your_api_key" \<br>
                    &nbsp;&nbsp;&nbsp;&nbsp; {{ request()->getSchemeAndHttpHost() }}/api/emails
                </div>
                <p class="text-sm text-muted" style="margin-top:.75rem;">
                    Create API keys via <a href="/admin/api-keys" class="text-link">Settings &gt; API Keys</a> or via the API itself.
                </p>
            </div>
        </div>

        {{-- Rate Limiting --}}
        <div id="rate-limiting" class="card">
            <div class="card__header">
                <span class="card__header-title">Rate Limiting</span>
            </div>
            <div class="card__body">
                <p>Default limit: <strong>10 requests per second</strong> per API key. Exceeding the limit returns a <code>429</code> response.</p>
            </div>
        </div>

        {{-- Error Handling --}}
        <div id="error-handling" class="card">
            <div class="card__header">
                <span class="card__header-title">Error Handling</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">All errors return a JSON object:</p>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;margin-bottom:1rem;">
                    {"statusCode": 401, "message": "Missing API key", "name": "missing_api_key"}
                </div>
                <table class="tbl">
                    <thead>
                        <tr><th>Code</th><th>Meaning</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>401</code></td><td>Missing API key</td></tr>
                        <tr><td><code>403</code></td><td>Invalid API key</td></tr>
                        <tr><td><code>404</code></td><td>Resource not found</td></tr>
                        <tr><td><code>422</code></td><td>Validation error</td></tr>
                        <tr><td><code>429</code></td><td>Rate limit exceeded</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- POST /emails --}}
        <div id="send-email" class="card">
            <div class="card__header">
                <span class="card__header-title">POST /emails</span>
                <span class="badge badge--info">Send Email</span>
            </div>
            <div class="card__body">
                <div style="overflow-x:auto;margin-bottom:1rem;">
                    <table class="tbl">
                        <thead>
                            <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>from</code></td><td>string</td><td>Yes</td><td>Sender, e.g. <code>"Name &lt;email@domain.com&gt;"</code></td></tr>
                            <tr><td><code>to</code></td><td>string | string[]</td><td>Yes</td><td>Recipient(s), max 50</td></tr>
                            <tr><td><code>subject</code></td><td>string</td><td>Yes</td><td>Subject line</td></tr>
                            <tr><td><code>html</code></td><td>string</td><td>No</td><td>HTML body</td></tr>
                            <tr><td><code>text</code></td><td>string</td><td>No</td><td>Plain text (auto-generated from HTML)</td></tr>
                            <tr><td><code>cc</code></td><td>string | string[]</td><td>No</td><td>CC recipients</td></tr>
                            <tr><td><code>bcc</code></td><td>string | string[]</td><td>No</td><td>BCC recipients</td></tr>
                            <tr><td><code>reply_to</code></td><td>string | string[]</td><td>No</td><td>Reply-to address(es)</td></tr>
                            <tr><td><code>headers</code></td><td>object</td><td>No</td><td>Custom headers</td></tr>
                            <tr><td><code>tags</code></td><td>array</td><td>No</td><td>Metadata tags</td></tr>
                            <tr><td><code>scheduled_at</code></td><td>datetime</td><td>No</td><td>Scheduled send time (ISO 8601)</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-sm text-muted">Response: <code>{"id": "uuid-of-the-email"}</code></p>
            </div>
        </div>


        {{-- POST /emails/batch --}}
        <div id="send-batch" class="card">
            <div class="card__header">
                <span class="card__header-title">POST /emails/batch</span>
                <span class="badge badge--info">Batch Send</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">Send up to <strong>100 emails</strong> in a single request. Each email is processed independently in the queue.</p>
                <p style="margin-bottom:.75rem;">Request body is an <strong>array</strong> of email objects (same schema as POST /emails):</p>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;margin-bottom:.75rem;white-space:pre;">[
  {"from": "App <noreply@example.com>", "to": "a@example.com", "subject": "Hello A", "html": "<p>Hi A</p>"},
  {"from": "App <noreply@example.com>", "to": "b@example.com", "subject": "Hello B", "html": "<p>Hi B</p>"}
]</div>
                <p class="text-sm text-muted">Response: <code>{"data": [{"id": "uuid-1"}, {"id": "uuid-2"}]}</code></p>
            </div>
        </div>

        {{-- GET /emails --}}
        <div id="get-emails" class="card">
            <div class="card__header">
                <span class="card__header-title">GET /emails</span>
                <span class="badge badge--neutral">List Emails</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">Returns a paginated list of outbound emails, newest first.</p>
                <table class="tbl">
                    <thead><tr><th>Parameter</th><th>Type</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>cursor</code></td><td>string</td><td>Cursor for pagination (from <code>next_cursor</code> in response)</td></tr>
                    </tbody>
                </table>
                <p class="text-sm text-muted" style="margin-top:.75rem;">Response includes <code>data</code> array, <code>next_cursor</code>, and <code>prev_cursor</code>.</p>
            </div>
        </div>

        {{-- GET /emails/:id --}}
        <div id="get-email" class="card">
            <div class="card__header">
                <span class="card__header-title">GET /emails/:id</span>
                <span class="badge badge--neutral">Get Email</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">Retrieve the full details of a specific email, including content and delivery status.</p>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;margin-bottom:.75rem;white-space:pre;">{
  "id": "019d39bc-5508-7356-8c58-bb93aa3f76f9",
  "from": "CLOM <noreply@code-labs.nl>",
  "to": ["user@example.com"],
  "subject": "Welcome!",
  "status": "sent",
  "html": "<h1>Welcome!</h1>",
  "created_at": "2026-03-29T13:15:37.000000Z",
  "sent_at": "2026-03-29T13:15:39.000000Z"
}</div>
                <p class="text-sm text-muted">Additional endpoints:</p>
                <table class="tbl" style="margin-top:.5rem;">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>PATCH</code></td><td><code>/emails/:id</code></td><td>Update a scheduled email (change <code>scheduled_at</code>)</td></tr>
                        <tr><td><code>DELETE</code></td><td><code>/emails/:id</code></td><td>Cancel a scheduled email</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Domains --}}
        <div id="domains-api" class="card">
            <div class="card__header">
                <span class="card__header-title">Domains</span>
            </div>
            <div class="card__body">
                <table class="tbl">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>GET</code></td><td><code>/domains</code></td><td>List all domains</td></tr>
                        <tr><td><code>POST</code></td><td><code>/domains</code></td><td>Add a domain <code>{"name": "example.com"}</code></td></tr>
                        <tr><td><code>GET</code></td><td><code>/domains/:id</code></td><td>Get domain details + DNS records</td></tr>
                        <tr><td><code>POST</code></td><td><code>/domains/:id/verify</code></td><td>Verify DNS (SPF, DKIM, DMARC, MX)</td></tr>
                        <tr><td><code>DELETE</code></td><td><code>/domains/:id</code></td><td>Remove a domain</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- API Keys --}}
        <div id="api-keys-api" class="card">
            <div class="card__header">
                <span class="card__header-title">API Keys</span>
            </div>
            <div class="card__body">
                <table class="tbl">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>GET</code></td><td><code>/api-keys</code></td><td>List API keys (key prefix only)</td></tr>
                        <tr><td><code>POST</code></td><td><code>/api-keys</code></td><td>Create key <code>{"name": "...", "permission": "full_access"}</code></td></tr>
                        <tr><td><code>DELETE</code></td><td><code>/api-keys/:id</code></td><td>Revoke an API key</td></tr>
                    </tbody>
                </table>
                <p class="text-sm text-muted" style="margin-top:.75rem;">The full API key token is only returned once on creation. Store it securely.</p>
            </div>
        </div>
        {{-- SDK Examples --}}
        <div id="sdk-curl" class="card">
            <div class="card__header">
                <span class="card__header-title">SDK Examples</span>
            </div>
            <div class="card__body">

                {{-- cURL --}}
                <h4 class="font-semibold" style="margin-bottom:.5rem;">cURL</h4>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;margin-bottom:1.25rem;white-space:pre;">curl -X POST {{ request()->getSchemeAndHttpHost() }}/api/emails \
  -H "Authorization: Bearer $CLOM_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "from": "App <noreply@code-labs.nl>",
    "to": "user@example.com",
    "subject": "Welcome!",
    "html": "&lt;h1&gt;Welcome to our service&lt;/h1&gt;"
  }'</div>

                {{-- PHP --}}
                <h4 id="sdk-php" class="font-semibold" style="margin-bottom:.5rem;">PHP / Laravel</h4>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;margin-bottom:1.25rem;white-space:pre;">$response = Http::withToken($apiKey)
    ->post('{{ request()->getSchemeAndHttpHost() }}/api/emails', [
        'from'    => 'App <noreply@code-labs.nl>',
        'to'      => 'user@example.com',
        'subject' => 'Welcome!',
        'html'    => '<h1>Welcome!</h1>',
    ]);

$emailId = $response->json('id');</div>

                {{-- Node.js --}}
                <h4 id="sdk-node" class="font-semibold" style="margin-bottom:.5rem;">Node.js</h4>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;margin-bottom:1.25rem;white-space:pre;">const res = await fetch('{{ request()->getSchemeAndHttpHost() }}/api/emails', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    from: 'App <noreply@code-labs.nl>',
    to: 'user@example.com',
    subject: 'Welcome!',
    html: '<h1>Welcome!</h1>',
  }),
});
const { id } = await res.json();</div>

                {{-- Python --}}
                <h4 id="sdk-python" class="font-semibold" style="margin-bottom:.5rem;">Python</h4>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;overflow-x:auto;margin-bottom:0;white-space:pre;">import requests

response = requests.post(
    '{{ request()->getSchemeAndHttpHost() }}/api/emails',
    headers={'Authorization': f'Bearer {api_key}'},
    json={
        'from': 'App <noreply@code-labs.nl>',
        'to': 'user@example.com',
        'subject': 'Welcome!',
        'html': '<h1>Welcome!</h1>',
    }
)
email_id = response.json()['id']</div>

            </div>
        </div>

    </div>
</div>
@endsection
