# Minori-kun Marketplace

Minori-kun is a Japanese agricultural marketplace implemented as a Laravel modular-monolith API with three independent Vue applications in one pnpm monorepo.

Local development runs directly on the host. Docker is not used for development.

## Project structure

```text
apps/
├── api/             Laravel JSON API
├── buyer-web/       Buyer Vue SPA
├── producer-web/    Producer Vue SPA
└── admin-web/       Admin Vue SPA
packages/
├── ui/              Shared UI primitives and design tokens
├── api-client/      Axios client and generated API types
└── config/          Shared frontend tooling configuration
docs/                Requirements, workbook, and project context
scripts/             Cross-platform project verification scripts
```

The files under `docker/` are production image definitions only. They are not part of setup or daily local development.

## Local prerequisites

Install these tools on the host and make them available on `PATH`:

- PHP 8.4 with `bcmath`, `curl`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, and `zip` extensions.
- Composer 2.8 or newer.
- Node.js 22.12 or newer.
- pnpm 10. Enable it with `corepack enable` and `corepack prepare pnpm@10.17.1 --activate` if Corepack is available.
- MySQL 8.4 with a local database named `minori`.
- Mailpit for browser-based local email inspection, or use Laravel's `log` mailer instead.
- PowerShell 5.1 or newer.

Verify the main tools:

```powershell
php --version
composer --version
node --version
pnpm --version
mysql --version
```

## Database preparation

Create a UTF-8 local database and a development-only MySQL user. The default application settings expect:

```text
Host:     127.0.0.1
Port:     3306
Database: minori
User:     minori
Password: minori_local
```

You may use different local credentials. Put them only in `apps/api/.env`; never commit that file.

## First setup

From the repository root:

```powershell
.\m.ps1 bootstrap
```

The command performs the following work in order:

1. Validates the PHP, Composer, Node.js, and pnpm toolchain.
2. Installs Composer dependencies from `composer.lock`.
3. Creates `apps/api/.env` from `.env.example` when it does not exist.
4. Generates the Laravel application key and storage link.
5. Installs pnpm dependencies from `pnpm-lock.yaml`.
6. Runs pending migrations against the configured MySQL database.
7. Loads repeatable local fixtures without duplicating them.

`bootstrap` does not start long-running development servers.

## Daily development

Start local MySQL first. Then use separate terminals so logs remain understandable.

Terminal 1 — Laravel API:

```powershell
cd apps/api
php artisan serve
```

Terminal 2 — all three Vue applications with hot reload:

```powershell
.\m.ps1 web
```

Terminal 3 — local email inbox, when needed:

```powershell
mailpit
```

Terminal 4 — database queue worker, only when developing an asynchronous feature:

```powershell
cd apps/api
php artisan queue:work
```

Local URLs:

| Application | URL |
|---|---|
| API | http://localhost:8000 |
| Buyer | http://localhost:5173 |
| Producer | http://localhost:5174 |
| Admin | http://localhost:5175 |
| Mailpit | http://localhost:8025 |

Stop a foreground server or worker with `Ctrl+C`.

## Project commands

```powershell
.\m.ps1 bootstrap
.\m.ps1 web
.\m.ps1 test
.\m.ps1 check
.\m.ps1 api-sync
.\m.ps1 help
```

- `bootstrap` coordinates both Composer and pnpm setup, then initializes the application.
- `web` runs the Buyer, Producer, and Admin Vite servers together.
- `test` runs Pest and frontend unit tests.
- `check` runs Pint check, PHPStan/Larastan, Pest, ESLint, Prettier check, TypeScript checks, Vitest, frontend production builds, and non-mutating API contract freshness checks.
- `api-sync` intentionally regenerates `apps/api/openapi.json` and the committed TypeScript API declarations.

Normal Laravel operations use normal Artisan commands from `apps/api`:

```powershell
cd apps/api
php artisan route:list
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan migrate:fresh --seed
```

`migrate:fresh --seed` is destructive and must only be used when all local database data may be deleted.

## pnpm workspace and package installation

The root and application-level `node_modules` directories are normal in a pnpm workspace. They are dependency links/views created by pnpm, not three unrelated full installations. Do not edit or delete individual package files inside them, and never commit them.

Run package commands from the repository root. To install a runtime dependency for Buyer only:

```powershell
pnpm --filter @minorikun/buyer-web add <package-name>
```

To install a Buyer-only development dependency:

```powershell
pnpm --filter @minorikun/buyer-web add -D <package-name>
```

Other workspace targets follow the same pattern:

```powershell
pnpm --filter @minorikun/producer-web add <package-name>
pnpm --filter @minorikun/admin-web add <package-name>
pnpm --filter @minorikun/ui add <package-name>
pnpm --filter @minorikun/api-client add <package-name>
```

Install repository-wide tooling at the workspace root only when every application needs it:

```powershell
pnpm add -Dw <package-name>
```

These commands update the selected package's `package.json` and the single root `pnpm-lock.yaml`. Do not use `npm install` inside an individual application.

## Local runtime behavior

- Sanctum uses encrypted first-party session cookies; no JWT or browser-stored secret is used.
- Sessions, cache entries, and queued jobs are stored in MySQL.
- The queue worker is opt-in during development.
- Database and application timestamps remain UTC.
- Marketplace business dates and UI display use `BUSINESS_TIMEZONE=Asia/Tokyo`.
- Mailpit is a local inspection tool, not a production email provider.

If Mailpit is not installed, set this in `apps/api/.env` and inspect `apps/api/storage/logs/laravel.log`:

```dotenv
MAIL_MAILER=log
```

## Local demo accounts

The local seeder creates:

| Portal | Email | Password |
|---|---|---|
| Buyer | `buyer@example.test` | `password` |
| Producer | `producer@example.test` | `password` |

No default Admin credential is seeded.

## API contract workflow

Laravel and Scramble generate OpenAPI. `openapi-typescript` converts the committed contract into shared frontend declarations.

1. Change the Laravel API contract intentionally.
2. Run `.\m.ps1 api-sync`.
3. Review `apps/api/openapi.json` and `packages/api-client/src/generated/schema.d.ts`.
4. Run `.\m.ps1 check`.

The freshness check generates temporary comparison files outside the repository and does not rewrite tracked files.

## Development guidelines

- Read the relevant requirement and acceptance-test IDs in `docs/REQUIREMENTS.md` before implementation.
- Keep Laravel controllers thin. Use Form Requests, Policies, domain actions/services, and API Resources.
- Enforce portal role and resource ownership on the server. Frontend route guards are UX only.
- Wrap multi-record financial and inventory changes in database transactions.
- Keep payment, refund, screening, fulfillment, and payout states independent and idempotent.
- Dispatch queued side effects only after the authoritative transaction commits.
- Keep role-specific pages inside their SPA. `packages/ui` contains shared primitives only.
- Pages use endpoint functions from `packages/api-client`; they do not call raw Axios directly.
- Use Vue Query for server state and Pinia for client/presentation state only.
- Store JPY as integers and percentages as basis points.
- Keep Producer-funded discounts separate from the Company's fixed 10% commission.
- Never commit credentials, real personal/payment/bank data, `vendor`, `node_modules`, logs, screenshots, or build output.

See `AGENTS.md` for the complete repository rules and required handoff format.

## Troubleshooting

- **PowerShell blocks `m.ps1`:** use `powershell -NoProfile -ExecutionPolicy Bypass -File .\m.ps1 help` for a one-off run, or apply your organization's approved script policy.
- **`php` version check fails:** install PHP 8.4 and ensure it appears before older PHP versions on `PATH`.
- **Composer cannot reach GitHub:** verify the host network/proxy and GitHub access, then rerun `bootstrap`. Composer no longer runs inside a container.
- **`pnpm install` reports `EACCES` inside a workspace `node_modules`:** rerun `bootstrap`. It detects and removes generated Linux-style dependency links left by the former Docker development environment, then recreates them for native Windows development.
- **Laravel reports that `public/storage` already exists:** `bootstrap` keeps a working native link and replaces only an inaccessible link left by the former Docker environment. It stops instead of deleting the path when `public/storage` is a real directory.
- **MySQL connection fails:** start MySQL and verify `DB_HOST`, `DB_PORT`, database, username, and password in `apps/api/.env`.
- **A port is busy:** stop the conflicting host process or change the relevant Laravel/Vite port configuration.
- **Schema is behind:** run `php artisan migrate` from `apps/api`.
- **Generated API files are stale:** run `.\m.ps1 api-sync`, review the changes, and rerun `.\m.ps1 check`.

## Sources of truth

- Product and system requirements: `docs/REQUIREMENTS.md`
- Manager workbook: `docs/みのりくん_EC画面要件定義書.xlsx`
- Current implementation context: `docs/PROJECT_CONTEXT.md`
- Repository agent rules: `AGENTS.md`
