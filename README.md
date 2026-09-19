# DevPulse

Developer API and webhook monitoring platform. Schedule HTTP health checks, capture inbound webhooks, track incidents, and optionally send Telegram alerts.

This is a **learning / portfolio** project, not a production SaaS. Use it to explore Laravel queues, scheduling, Inertia + Vue, policies, and outbound notifications.

## Stack

- PHP 8.3+, Laravel 13
- Inertia 3 + Vue 3 + Tailwind 4
- PostgreSQL or SQLite
- Queue worker + scheduler (required for automatic checks)
- Laravel Reverb (optional; live webhook inbox)

## Quick start

```bash
composer setup
```

Or step by step:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Run the app locally (HTTP + queue + logs + Vite):

```bash
composer run dev
```

Automatic monitor checks also need the scheduler. In a separate terminal:

```bash
php artisan schedule:work
```

`composer run dev` already includes a queue worker. Without a worker and scheduler, monitors only update when you click **Check now**.

### Frontend / Wayfinder

Generated Wayfinder TypeScript under `resources/js/actions` and `resources/js/routes` is gitignored. Create them with:

```bash
php artisan wayfinder:generate
# or as part of
npm run build
```

### Demo data (local only)

```bash
php artisan db:seed
```

Creates `test@example.com` / `password`, sample projects, and a demo webhook inbox (no monitors — create your own).

## Important environment flags

| Variable | Purpose |
| --- | --- |
| `REGISTRATION_ENABLED` | Default `false`. When `false`, `/register` returns 404 and Sign up links are hidden. Set `true` for local demos. |
| `TELEGRAM_BOT_TOKEN` / `TELEGRAM_CHAT_ID` | Optional. When set, ops alerts use Telegram. When empty, the same alerts go by email. |
| `MAIL_MAILER` / `RESEND_API_KEY` | Use `MAIL_MAILER=resend` and a Resend API key for real email (contact form + ops alerts). |
| `CONTACT_MAIL_TO` | Inbox for contact form and email ops alerts (falls back to `MAIL_FROM_ADDRESS`). |
| `APP_DEBUG` | Keep `false` outside local development. |

See `.env.example` and `docker/.env.example` for the full list.

## Features

- **Projects** — own monitors and webhook inboxes
- **HTTP monitors** — interval checks via queue + `monitors:dispatch-due`
- **Incidents** — open/resolve on status transitions; Telegram when configured
- **Webhook inbox** — `/hooks/{token}`, optional HMAC, GeoIP, live Echo feed
- **Contact** — email via the configured mailer (+ Telegram when configured), rate-limited
- **Ops alerts** — Telegram if configured; otherwise email (Resend-ready) for monitor created / down / recovered

## Security notes

- Registration is **off by default** so strangers cannot create monitors that fire HTTP from your server.
- Monitor URLs must be public `http`/`https`. Localhost and private/reserved IP literals (e.g. `127.0.0.1`, `10.x`, cloud metadata) are rejected. That protects the **DevPulse host**, not your customers' public APIs.
- Projects and monitors are owned by a single user (policies). Another account cannot operate your monitors.
- Passwords require at least 8 characters, mixed case, and a number. The register/reset forms show the rules up front.

## Tests

```bash
php artisan test --compact
# or the project CI script
composer ci:check
```

## License

MIT — see [LICENSE](LICENSE).
