# Changelog

All notable changes to CLOM will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-03-29

### Added
- Resend-compatible REST API (14 endpoints)
  - `POST /api/emails` — Send email
  - `POST /api/emails/batch` — Batch send (up to 100)
  - `GET /api/emails` — List emails
  - `GET /api/emails/:id` — Get email details
  - `PATCH /api/emails/:id` — Update scheduled email
  - `DELETE /api/emails/:id` — Cancel scheduled email
  - Domain CRUD with DNS verification (SPF, DKIM, DMARC, MX)
  - API key management with permissions
- Inbound SMTP server
  - PHP 8.4 Fibers-based async server
  - DKIM signature verification
  - SPF record validation
  - Based on OceanMail, modernized for PHP 8.4
- Webmail client
  - Inbox, Sent, Starred, Drafts, Trash folders
  - Compose with CC/BCC support
  - Email detail view with thread support
  - Search functionality
- AI assistant (BYOK - Bring Your Own Key)
  - Multi-provider support: Anthropic, OpenAI, Gemini, Ollama
  - Compose, reply, and summarize capabilities
  - Laravel AI SDK integration
  - AI Settings page for provider configuration
- Email workflows
  - Visual workflow builder
  - 7 trigger operators (contains, equals, starts_with, ends_with, regex, is_true, is_false)
  - 8 action types (auto_reply, ai_reply, forward, label, move, mark_read, star, webhook)
  - Workflow logging and statistics
- Admin panel (DaisyUI + Tailwind CSS v4)
  - Dashboard with statistics
  - Email logs with filtering
  - Domain management
  - API key management
  - Documentation pages (Admin Guide + API Docs)
  - Swagger UI (OpenAPI 3.1)
- Testing: 18 Pest tests, 29 assertions
- Rate limiting (10 req/s per API key)
- Idempotency key support
- Queue-based async email sending via Redis
