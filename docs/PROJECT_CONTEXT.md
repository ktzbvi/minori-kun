# Minori-kun Marketplace — Current Project Context

Last updated: 2026-09-04 (Asia/Tokyo)

## Product and design

- Minori-kun is a Japanese agricultural marketplace with Buyer, Producer, and Admin roles.
- The canonical product and system source is `docs/REQUIREMENTS.md`.
- The current manager workbook is `docs/みのりくん_EC画面要件定義書.xlsx`.
- The Figma file is [EC Site Screen Mockups - Laravel Vue](https://www.figma.com/design/gJ6crERB0VxDJfQuQYZv76/EC-Site-Screen-Mockups---Laravel-Vue?node-id=0-1); the active design page is `V2`.
- The Canva flow is [Minori-kun design flow](https://canva.link/pxwsss4kv50d3ff).

## Confirmed functional baseline

- The inventory contains 47 logical screens: Buyer `B01–B21`, Producer `P01–P15`, and Admin `A01–A11`.
- Producers are sellers; the company operates the marketplace and receives a 10% sales commission.
- The product-level customer discount is separate from the company commission.
- PAY.JP Platform Marketplace API v1 is the payment and Producer onboarding provider baseline.
- Role access and resource ownership are enforced server-side; Producers may access only their own resources.
- Payment, refund, screening, payout, and sensitive administrative changes require authoritative state, idempotency, and safe audit records.

## Implementation foundation

- The Git repository has been reset to a new `main` branch with no inherited commit history.
- The selected architecture is one monorepo containing one Laravel modular-monolith API and three independent Vue applications.
- The intended frontend stack is Vue 3, TypeScript, Vite, Vue Router, Pinia, Tailwind CSS, and shadcn-vue.
- Shared frontend code is limited to UI primitives, design tokens, API client/types, and tooling configuration; role-specific pages and workflows remain in their applications.
- The backend is a Laravel 13 modular monolith and the three portals are Vue 3 applications managed by one pnpm workspace.
- Local development runs directly on the host and does not use Docker. Developers install PHP 8.4, Composer, Node.js, pnpm, MySQL, and optionally Mailpit locally.
- The root `m.ps1` script is limited to cross-workspace coordination: bootstrap, all-frontend development, combined tests/checks, and API contract synchronization. Normal Laravel, Composer, pnpm filter, MySQL, and Mailpit operations use their native commands.
- Sessions, application cache, and queued jobs use MySQL. Redis and Laravel Horizon were removed to keep local and initial hosting costs proportionate to the confirmed workload.
- A queue worker is intentionally opt-in through Laravel's native `php artisan queue:work` command.
- Laravel scheduling remains available and must be attached to a production cron or hosting scheduler only when a scheduled feature is implemented.
- Database timestamps and Laravel's application timezone remain UTC. `BUSINESS_TIMEZONE=Asia/Tokyo` is the explicit boundary for marketplace business dates and customer-facing display.
- Mailpit is local-only email capture and is not a production mail provider.
- Local MySQL data and Laravel uploads live on the host. Composer `vendor` and pnpm `node_modules` remain untracked development dependencies.
- Files under `docker/` define production images only and are not part of local setup or daily development.

## Continuation rule

Read `docs/REQUIREMENTS.md` and the consolidated `AGENTS.md` before implementing a domain. Historical session transcripts, generated previews, temporary scripts, and machine-specific MCP configurations are retained under `C:\EC-legacy-archive-2026-09-02` and are not active implementation sources.
