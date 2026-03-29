@extends('layouts.admin')
@section('title', 'Admin Guide')

@section('content')
<h2 class="text-2xl font-bold mb-6">Admin Guide</h2>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- TOC -->
    <div class="lg:col-span-1">
        <ul class="menu bg-base-100 rounded-box shadow w-full">
            <li class="menu-title">Inhoudsopgave</li>
            <li><a href="#overzicht">Overzicht</a></li>
            <li><a href="#mail-client">Mail Client</a></li>
            <li><a href="#workflows">Workflows</a></li>
            <li><a href="#ai-assistent">AI Assistent</a></li>
            <li><a href="#domeinen">Domeinen instellen</a></li>
            <li><a href="#api-keys-guide">API Keys beheren</a></li>
            <li><a href="#smtp-server">SMTP Server</a></li>
            <li><a href="#troubleshooting">Troubleshooting</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="lg:col-span-3 space-y-6">

        <div id="overzicht" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Overzicht</h3>
            <p>CLOM (CodeLabs Open Mailer) is een self-hosted, Resend-compatible email platform. Het biedt:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li><strong>REST API</strong> — Stuur e-mails vanuit elke applicatie</li>
                <li><strong>Mail Client</strong> — Webmail interface met inbox, verzenden en zoeken</li>
                <li><strong>AI Assistent</strong> — Claude-aangedreven hulp bij schrijven en antwoorden</li>
                <li><strong>Workflows</strong> — Automatiseer acties op inkomende e-mails</li>
                <li><strong>Inbound SMTP</strong> — Ontvang e-mails direct op je server</li>
                <li><strong>DNS Verificatie</strong> — SPF, DKIM, DMARC controle</li>
            </ul>
        </div>

        <div id="mail-client" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Mail Client</h3>
            <p class="mb-3">De ingebouwde mail client biedt een volwaardige webmail-ervaring:</p>

            <div class="space-y-3">
                <div class="collapse collapse-arrow bg-base-200">
                    <input type="checkbox" checked />
                    <div class="collapse-title font-medium">Inbox & Mappen</div>
                    <div class="collapse-content">
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Ga naar <strong>Mail Client > Inbox</strong> in het menu</li>
                            <li>Gebruik de mappen-sidebar: Inbox, Verzonden, Met ster, Concepten, Prullenbak</li>
                            <li>Klik op een e-mail om deze te openen en te lezen</li>
                            <li>Ongelezen berichten worden vetgedrukt weergegeven</li>
                            <li>Gebruik de zoekbalk om te zoeken op onderwerp, afzender of inhoud</li>
                        </ul>
                    </div>
                </div>

                <div class="collapse collapse-arrow bg-base-200">
                    <input type="checkbox" />
                    <div class="collapse-title font-medium">E-mail opstellen</div>
                    <div class="collapse-content">
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Klik op <strong>"+ Nieuwe mail"</strong> of ga naar <strong>Opstellen</strong></li>
                            <li>Vul Van, Aan, CC (optioneel), Onderwerp en bericht in</li>
                            <li>Gebruik de <strong>AI Assistent</strong> rechts om hulp te krijgen bij het schrijven</li>
                            <li>Klik <strong>"Verstuur"</strong> — de mail gaat via de queue naar Kerio Connect</li>
                        </ul>
                    </div>
                </div>

                <div class="collapse collapse-arrow bg-base-200">
                    <input type="checkbox" />
                    <div class="collapse-title font-medium">Beantwoorden</div>
                    <div class="collapse-content">
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Open een ontvangen e-mail en klik <strong>"Beantwoord"</strong></li>
                            <li>De originele mail wordt automatisch geciteerd</li>
                            <li>Gebruik AI-snelknoppen: "Professioneel antwoord", "Bevestiging", etc.</li>
                            <li>Of beschrijf in eigen woorden wat je wilt zeggen en laat AI het uitwerken</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="workflows" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Workflows</h3>
            <p class="mb-3">Workflows automatiseren acties op inkomende e-mails.</p>

            <h4 class="font-semibold mt-4 mb-2">Triggers (Condities)</h4>
            <table class="table table-sm">
                <thead><tr><th>Veld</th><th>Operators</th><th>Voorbeeld</th></tr></thead>
                <tbody>
                    <tr><td>Van (afzender)</td><td>bevat, is gelijk aan, eindigt met</td><td><code>eindigt met @overheid.nl</code></td></tr>
                    <tr><td>Aan (ontvanger)</td><td>bevat, is gelijk aan</td><td><code>is gelijk aan support@code-labs.nl</code></td></tr>
                    <tr><td>Onderwerp</td><td>bevat, begint met, regex</td><td><code>bevat "factuur"</code></td></tr>
                    <tr><td>Inhoud</td><td>bevat, regex</td><td><code>bevat "urgent"</code></td></tr>
                    <tr><td>Heeft bijlage</td><td>is waar/onwaar</td><td><code>is waar</code></td></tr>
                    <tr><td>SPF/DKIM</td><td>is gelijk aan</td><td><code>is gelijk aan "pass"</code></td></tr>
                </tbody>
            </table>

            <h4 class="font-semibold mt-4 mb-2">Acties</h4>
            <table class="table table-sm">
                <thead><tr><th>Actie</th><th>Beschrijving</th><th>Parameters</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-primary badge-sm">auto_reply</span></td><td>Stuur automatisch antwoord</td><td>HTML template</td></tr>
                    <tr><td><span class="badge badge-primary badge-sm">ai_reply</span></td><td>Laat AI een antwoord genereren</td><td>Instructies + auto_send</td></tr>
                    <tr><td><span class="badge badge-primary badge-sm">forward</span></td><td>Stuur door naar ander adres</td><td>E-mailadres</td></tr>
                    <tr><td><span class="badge badge-primary badge-sm">label</span></td><td>Verplaats naar map</td><td>Mapnaam</td></tr>
                    <tr><td><span class="badge badge-primary badge-sm">webhook</span></td><td>HTTP POST naar externe URL</td><td>URL</td></tr>
                    <tr><td><span class="badge badge-primary badge-sm">mark_read</span></td><td>Markeer als gelezen</td><td>-</td></tr>
                    <tr><td><span class="badge badge-primary badge-sm">star</span></td><td>Voeg ster toe</td><td>-</td></tr>
                </tbody>
            </table>

            <div class="alert alert-info mt-4">
                <span><strong>Tip:</strong> Workflows gebruiken AND-logica voor triggers. Alle condities moeten matchen. Maak meerdere workflows voor OR-logica.</span>
            </div>
        </div>

        <div id="ai-assistent" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">AI Assistent</h3>
            <p class="mb-3">CLOM integreert met Claude (Anthropic) voor slimme e-mailfuncties:</p>
            <ul class="list-disc list-inside space-y-1">
                <li><strong>Samenvatten</strong> — Open een e-mail en klik "AI Samenvatting"</li>
                <li><strong>Opstellen</strong> — Beschrijf wat je wilt zeggen, AI schrijft het uit</li>
                <li><strong>Beantwoorden</strong> — AI leest de originele mail en stelt een antwoord voor</li>
                <li><strong>Workflow AI Reply</strong> — Automatisch AI-gegenereerde antwoorden op inkomende mail</li>
            </ul>
            <div class="alert alert-warning mt-4">
                <span>Stel je Anthropic API key in via <code>.env</code>: <code>ANTHROPIC_API_KEY=sk-ant-xxx</code></span>
            </div>
        </div>

        <div id="domeinen" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Domeinen instellen</h3>
            <ol class="list-decimal list-inside space-y-2">
                <li>Ga naar <strong>Beheer > Domeinen</strong></li>
                <li>Klik <strong>"+ Domein toevoegen"</strong> en voer je domeinnaam in</li>
                <li>CLOM genereert de benodigde DNS-records (SPF, DKIM, DMARC, MX)</li>
                <li>Voeg deze records toe bij je DNS-provider (bijv. TransIP, Cloudflare)</li>
                <li>Klik <strong>"Verifieer DNS"</strong> om de records te controleren</li>
                <li>Groene badges = correct geconfigureerd</li>
            </ol>
        </div>

        <div id="smtp-server" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">SMTP Server</h3>
            <p class="mb-3">CLOM heeft een ingebouwde SMTP-server voor het ontvangen van e-mails:</p>
            <div class="mockup-code text-sm">
                <pre data-prefix="$"><code>php artisan smtp:serve --port=25</code></pre>
            </div>
            <p class="mt-3 text-sm">De SMTP server draait via Supervisor en start automatisch. Inkomende e-mails worden:</p>
            <ul class="list-disc list-inside mt-2 space-y-1 text-sm">
                <li>Gevalideerd (SPF + DKIM verificatie)</li>
                <li>Opgeslagen in de database</li>
                <li>Verwerkt door actieve workflows</li>
                <li>Getoond in de Mail Client inbox</li>
            </ul>
        </div>

        <div id="troubleshooting" class="bg-base-100 rounded-box shadow p-6">
            <h3 class="text-lg font-bold mb-3">Troubleshooting</h3>
            <div class="space-y-3">
                <div class="collapse collapse-arrow bg-base-200">
                    <input type="checkbox" />
                    <div class="collapse-title font-medium text-sm">Mails worden niet verstuurd</div>
                    <div class="collapse-content text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Controleer <code>.env</code> MAIL_* settings</li>
                            <li>Check of queue workers draaien: <code>supervisorctl status</code></li>
                            <li>Bekijk failed jobs: <code>php artisan queue:failed</code></li>
                            <li>Bekijk logs: <code>tail -f storage/logs/laravel.log</code></li>
                        </ul>
                    </div>
                </div>
                <div class="collapse collapse-arrow bg-base-200">
                    <input type="checkbox" />
                    <div class="collapse-title font-medium text-sm">SMTP server start niet</div>
                    <div class="collapse-content text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Port 25 bezet? <code>ss -tlnp | grep :25</code></li>
                            <li>Postfix uitschakelen: <code>systemctl stop postfix</code></li>
                            <li>Supervisor logs: <code>cat /var/log/clom-smtp.log</code></li>
                        </ul>
                    </div>
                </div>
                <div class="collapse collapse-arrow bg-base-200">
                    <input type="checkbox" />
                    <div class="collapse-title font-medium text-sm">AI Assistent werkt niet</div>
                    <div class="collapse-content text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Stel <code>ANTHROPIC_API_KEY</code> in in <code>.env</code></li>
                            <li>Controleer of de API key geldig is</li>
                            <li>Check <code>config/services.php</code> voor de juiste configuratie</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
