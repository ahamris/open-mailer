# CLOM — CodeLabs Open Mailer

Self-hosted, Resend-compatible email API met AI-powered workflows.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-13-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-purple.svg)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-18%20passing-green.svg)](#testing)

## Wat is CLOM?

CLOM is een open source, self-hosted alternatief voor [Resend](https://resend.com), [Mailgun](https://mailgun.com) en [SendGrid](https://sendgrid.com). Gebouwd met Laravel 13 en ontworpen voor organisaties die controle willen over hun e-mailinfrastructuur.

**Perfect voor:**
- Nederlandse overheden (AVG/BIO2/NIS2 compliant)
- Bedrijven die self-hosted e-mail API nodig hebben
- Teams die AI-powered e-mailworkflows willen

## Features

### REST API (Resend-compatible)
- `POST /api/emails` — Verstuur e-mails
- `POST /api/emails/batch` — Bulk verzending (max 100)
- `GET /api/emails` — Lijst verzonden mails
- `GET /api/emails/:id` — Email details + status
- Domain management met SPF/DKIM/DMARC verificatie
- API key management met permissies
- Rate limiting (10 req/s)
- Idempotency keys

### Webmail Client
- Inbox, Verzonden, Met ster, Concepten, Prullenbak
- Compose met CC/BCC
- Thread-weergave
- Zoeken in e-mails

### AI Assistent (BYOK — Bring Your Own Key)
- **Compose** — Beschrijf wat je wilt, AI schrijft de mail
- **Reply** — AI leest de originele mail en stelt antwoord voor
- **Summarize** — Vat lange mails samen
- **Providers:** Anthropic (Claude), OpenAI (GPT), Google Gemini, Ollama (lokaal)
- Powered by [Laravel AI SDK](https://laravel.com/docs/13.x/ai-sdk)

### Inbound SMTP Server
- PHP 8.4 Fibers (async, non-blocking)
- DKIM signature verificatie
- SPF record validatie
- Gemoderniseerd van [OceanMail](https://github.com/nox7/oceanmail)

### Email Workflows
- Trigger op: afzender, ontvanger, onderwerp, inhoud, bijlagen, SPF/DKIM
- Acties: auto-reply, AI reply, doorsturen, labelen, webhook, markeer als gelezen
- Workflow logging en statistieken

### Admin Panel (DaisyUI)
- Dashboard met realtime statistieken
- E-mail logs met filtering
- Domain beheer met DNS verificatie
- API key management
- AI instellingen (multi-provider)
- Workflow builder
- Volledige documentatie + Swagger UI

## Tech Stack

| Component | Technologie |
|---|---|
| Framework | Laravel 13.2 |
| PHP | 8.4 |
| Frontend | Tailwind CSS v4 + DaisyUI 5.5 |
| Database | PostgreSQL 17 |
| Cache/Queue | Redis |
| AI SDK | Laravel AI SDK (Anthropic, OpenAI, Gemini, Ollama) |
| SMTP | Custom PHP Fibers-based server |
| Web Server | Nginx + PHP-FPM |
| Process Manager | Supervisor |
| Tests | Pest (18 tests, 29 assertions) |

## Installatie

### Vereisten

- PHP 8.4+
- PostgreSQL 17+
- Redis
- Node.js 22+
- Composer 2.9+

### Quick Start

```bash
# Clone
git clone https://github.com/ahamris/open-mailer.git
cd open-mailer

# Dependencies
composer install
npm install && npm run build

# Configuratie
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate

# Admin gebruiker
php artisan admin:create "Naam" "email@example.com" "wachtwoord"

# API key
php artisan apikey:create "Production"

# Start
php artisan serve
```

### Docker (komt binnenkort)

```bash
docker compose up -d
```

### Productie

```bash
# Nginx + PHP-FPM + Supervisor
# Zie docs/deployment.md voor volledige instructie

# Queue workers
supervisorctl start clom-queue:*

# SMTP server (port 25)
supervisorctl start clom-smtp
```

## API Gebruik

```bash
# Verstuur een email
curl -X POST https://your-server.com/api/emails \
  -H "Authorization: Bearer clom_your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "from": "App <noreply@your-domain.com>",
    "to": "user@example.com",
    "subject": "Welkom!",
    "html": "<h1>Welkom bij onze dienst</h1>"
  }'
```

### SDK Voorbeelden

**PHP / Laravel:**
```php
$response = Http::withToken($apiKey)
    ->post('https://your-server.com/api/emails', [
        'from' => 'App <noreply@your-domain.com>',
        'to' => 'user@example.com',
        'subject' => 'Welkom!',
        'html' => '<h1>Welkom!</h1>',
    ]);
```

**Node.js:**
```javascript
const res = await fetch('https://your-server.com/api/emails', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    from: 'App <noreply@your-domain.com>',
    to: 'user@example.com',
    subject: 'Welkom!',
    html: '<h1>Welkom!</h1>',
  }),
});
```

Volledige API documentatie: `/admin/docs/api` of `/admin/docs/swagger`

## Testing

```bash
./vendor/bin/pest
```

```
 PASS  Tests\Feature\AdminAuthTest (5 tests)
 PASS  Tests\Feature\ApiEmailTest (7 tests)
 PASS  Tests\Feature\ApiKeyTest (3 tests)
 PASS  Tests\Feature\WorkflowTest (3 tests)

 Tests: 18 passed (29 assertions)
```

## Roadmap

- [ ] Docker Compose setup
- [ ] DKIM signing voor outbound
- [ ] Contacts & audiences
- [ ] Broadcasts/nieuwsbrieven
- [ ] Template editor (drag & drop)
- [ ] Webhook delivery events
- [ ] Multi-tenant support
- [ ] Resend SDK drop-in compatibility

## Licentie

MIT License — zie [LICENSE](LICENSE)

## Credits

- Gebouwd door [CodeLabs B.V.](https://code-labs.nl)
- SMTP server gebaseerd op [OceanMail](https://github.com/nox7/oceanmail) door Garet C. Green
- AI powered by [Laravel AI SDK](https://laravel.com/docs/13.x/ai-sdk)
- UI componenten: [DaisyUI](https://daisyui.com)
