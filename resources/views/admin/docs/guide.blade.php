@extends('layouts.admin')
@section('title', 'Admin Guide')
@section('subtitle', 'Learn how to use CLOM effectively')

@section('content')
<div style="display:grid;grid-template-columns:14rem 1fr;gap:1.5rem;align-items:start;">

    {{-- Table of Contents --}}
    <div class="card" style="position:sticky;top:5rem;">
        <div class="card__body" style="padding:.75rem;">
            <p class="text-xs font-semibold" style="color:var(--text-tertiary);text-transform:uppercase;margin-bottom:.5rem;">Contents</p>
            <nav style="display:flex;flex-direction:column;gap:.125rem;">
                <a href="#overview" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Overview</a>
                <a href="#mail-client" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Mail Client</a>
                <a href="#workflows" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Workflows</a>
                <a href="#ai-assistant" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">AI Assistant</a>
                <a href="#domains" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Domains</a>
                <a href="#smtp-server" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">SMTP Server</a>
                <a href="#troubleshooting" class="text-link text-sm" style="padding:.25rem .5rem;border-radius:.375rem;">Troubleshooting</a>
            </nav>
        </div>
    </div>

    {{-- Content --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Overview --}}
        <div id="overview" class="card">
            <div class="card__header">
                <span class="card__header-title">Overview</span>
            </div>
            <div class="card__body">
                <p>CLOM (CodeLabs Open Mailer) is a self-hosted, Resend-compatible email platform. It provides:</p>
                <ul style="list-style:disc;padding-left:1.25rem;margin-top:.75rem;display:flex;flex-direction:column;gap:.375rem;">
                    <li><strong>REST API</strong> &mdash; Send emails from any application</li>
                    <li><strong>Mail Client</strong> &mdash; Webmail interface with inbox, compose, and search</li>
                    <li><strong>AI Assistant</strong> &mdash; AI-powered writing and reply suggestions</li>
                    <li><strong>Workflows</strong> &mdash; Automate actions on incoming emails</li>
                    <li><strong>Inbound SMTP</strong> &mdash; Receive emails directly on your server</li>
                    <li><strong>DNS Verification</strong> &mdash; SPF, DKIM, DMARC validation</li>
                </ul>
            </div>
        </div>

        {{-- Mail Client --}}
        <div id="mail-client" class="card">
            <div class="card__header">
                <span class="card__header-title">Mail Client</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:1rem;">The built-in mail client provides a full webmail experience:</p>

                <h4 class="font-semibold" style="margin-bottom:.5rem;">Inbox &amp; Folders</h4>
                <ul style="list-style:disc;padding-left:1.25rem;margin-bottom:1rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                    <li>Navigate to <strong>Mail Client &gt; Inbox</strong> in the sidebar</li>
                    <li>Use the folder sidebar: Inbox, Sent, Starred, Drafts, Trash</li>
                    <li>Click an email to open and read it</li>
                    <li>Unread messages are displayed in bold</li>
                    <li>Use the search bar to search by subject, sender, or content</li>
                </ul>

                <h4 class="font-semibold" style="margin-bottom:.5rem;">Composing Emails</h4>
                <ul style="list-style:disc;padding-left:1.25rem;margin-bottom:1rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                    <li>Click <strong>"Compose"</strong> in the sidebar</li>
                    <li>Fill in From, To, CC (optional), Subject, and message</li>
                    <li>Use the <strong>AI Assistant</strong> panel for writing help</li>
                    <li>Click <strong>"Send"</strong> &mdash; the email is queued for delivery</li>
                </ul>

                <h4 class="font-semibold" style="margin-bottom:.5rem;">Replying</h4>
                <ul style="list-style:disc;padding-left:1.25rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                    <li>Open a received email and click <strong>"Reply"</strong></li>
                    <li>The original message is automatically quoted</li>
                    <li>Use AI quick actions: "Professional reply", "Confirmation", etc.</li>
                    <li>Or describe what you want to say and let AI draft it for you</li>
                </ul>
            </div>
        </div>

        {{-- Workflows --}}
        <div id="workflows" class="card">
            <div class="card__header">
                <span class="card__header-title">Workflows</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:1rem;">Workflows automate actions on incoming emails based on trigger conditions.</p>

                <h4 class="font-semibold" style="margin-bottom:.5rem;">Triggers (Conditions)</h4>
                <div style="overflow-x:auto;margin-bottom:1.25rem;">
                    <table class="tbl">
                        <thead>
                            <tr><th>Field</th><th>Operators</th><th>Example</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>From (sender)</td><td>contains, equals, ends with</td><td><code>ends with @government.nl</code></td></tr>
                            <tr><td>To (recipient)</td><td>contains, equals</td><td><code>equals support@code-labs.nl</code></td></tr>
                            <tr><td>Subject</td><td>contains, starts with, regex</td><td><code>contains "invoice"</code></td></tr>
                            <tr><td>Body</td><td>contains, regex</td><td><code>contains "urgent"</code></td></tr>
                            <tr><td>Has attachment</td><td>is true / is false</td><td><code>is true</code></td></tr>
                            <tr><td>SPF / DKIM</td><td>equals</td><td><code>equals "pass"</code></td></tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="font-semibold" style="margin-bottom:.5rem;">Actions</h4>
                <div style="overflow-x:auto;">
                    <table class="tbl">
                        <thead>
                            <tr><th>Action</th><th>Description</th><th>Parameters</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><span class="badge badge--info">auto_reply</span></td><td>Send automatic reply</td><td>HTML template</td></tr>
                            <tr><td><span class="badge badge--info">ai_reply</span></td><td>Let AI generate a reply</td><td>Instructions + auto_send</td></tr>
                            <tr><td><span class="badge badge--info">forward</span></td><td>Forward to another address</td><td>Email address</td></tr>
                            <tr><td><span class="badge badge--info">label</span></td><td>Move to folder</td><td>Folder name</td></tr>
                            <tr><td><span class="badge badge--info">webhook</span></td><td>HTTP POST to external URL</td><td>URL</td></tr>
                            <tr><td><span class="badge badge--info">mark_read</span></td><td>Mark as read</td><td>&mdash;</td></tr>
                            <tr><td><span class="badge badge--info">star</span></td><td>Add star</td><td>&mdash;</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert--info" style="margin-top:1rem;margin-bottom:0;">
                    <strong>Tip:</strong> Workflows use AND logic for triggers. All conditions must match. Create multiple workflows for OR logic.
                </div>
            </div>
        </div>

        {{-- AI Assistant --}}
        <div id="ai-assistant" class="card">
            <div class="card__header">
                <span class="card__header-title">AI Assistant</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">CLOM integrates with AI providers for smart email features:</p>
                <ul style="list-style:disc;padding-left:1.25rem;display:flex;flex-direction:column;gap:.375rem;">
                    <li><strong>Summarize</strong> &mdash; Open an email and click "AI Summary"</li>
                    <li><strong>Compose</strong> &mdash; Describe what you want to say, AI writes it out</li>
                    <li><strong>Reply</strong> &mdash; AI reads the original email and suggests a reply</li>
                    <li><strong>Workflow AI Reply</strong> &mdash; Automatic AI-generated replies on incoming mail</li>
                </ul>
                <div class="alert alert--warning" style="margin-top:1rem;margin-bottom:0;">
                    Configure your AI provider via <a href="/admin/ai-settings" class="text-link font-semibold">Settings &gt; AI Settings</a>. Supported: Anthropic, OpenAI, Google Gemini, Ollama.
                </div>
            </div>
        </div>

        {{-- Domains --}}
        <div id="domains" class="card">
            <div class="card__header">
                <span class="card__header-title">Domains</span>
            </div>
            <div class="card__body">
                <ol style="list-style:decimal;padding-left:1.25rem;display:flex;flex-direction:column;gap:.5rem;">
                    <li>Go to <strong>Settings &gt; Domains</strong></li>
                    <li>Click <strong>"+ Add Domain"</strong> and enter your domain name</li>
                    <li>CLOM generates the required DNS records (SPF, DKIM, DMARC, MX)</li>
                    <li>Add these records at your DNS provider (e.g. TransIP, Cloudflare)</li>
                    <li>Click <strong>"Verify DNS"</strong> to check the records</li>
                    <li>Green badges = correctly configured</li>
                </ol>
            </div>
        </div>

        {{-- SMTP Server --}}
        <div id="smtp-server" class="card">
            <div class="card__header">
                <span class="card__header-title">SMTP Server</span>
            </div>
            <div class="card__body">
                <p style="margin-bottom:.75rem;">CLOM has a built-in SMTP server for receiving emails:</p>
                <div style="background:var(--n700);color:var(--n200);padding:.75rem 1rem;border-radius:.5rem;font-family:monospace;font-size:.8125rem;margin-bottom:.75rem;">
                    $ php artisan smtp:serve --port=25
                </div>
                <p class="text-sm" style="margin-bottom:.5rem;">The SMTP server runs via Supervisor and starts automatically. Incoming emails are:</p>
                <ul style="list-style:disc;padding-left:1.25rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                    <li>Validated (SPF + DKIM verification)</li>
                    <li>Stored in the database</li>
                    <li>Processed by active workflows</li>
                    <li>Displayed in the Mail Client inbox</li>
                </ul>
            </div>
        </div>

        {{-- Troubleshooting --}}
        <div id="troubleshooting" class="card">
            <div class="card__header">
                <span class="card__header-title">Troubleshooting</span>
            </div>
            <div class="card__body">
                <div style="display:flex;flex-direction:column;gap:1rem;">

                    <div>
                        <h4 class="font-semibold" style="margin-bottom:.375rem;">Emails are not being sent</h4>
                        <ul style="list-style:disc;padding-left:1.25rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                            <li>Check <code>.env</code> MAIL_* settings</li>
                            <li>Verify queue workers are running: <code>supervisorctl status</code></li>
                            <li>Check failed jobs: <code>php artisan queue:failed</code></li>
                            <li>Check logs: <code>tail -f storage/logs/laravel.log</code></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold" style="margin-bottom:.375rem;">SMTP server won't start</h4>
                        <ul style="list-style:disc;padding-left:1.25rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                            <li>Port 25 in use? <code>ss -tlnp | grep :25</code></li>
                            <li>Disable Postfix: <code>systemctl stop postfix</code></li>
                            <li>Supervisor logs: <code>cat /var/log/clom-smtp.log</code></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold" style="margin-bottom:.375rem;">AI Assistant not working</h4>
                        <ul style="list-style:disc;padding-left:1.25rem;display:flex;flex-direction:column;gap:.25rem;" class="text-sm">
                            <li>Configure your AI provider in <a href="/admin/ai-settings" class="text-link">AI Settings</a></li>
                            <li>Verify the API key is valid</li>
                            <li>Check <code>config/services.php</code> for correct configuration</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
