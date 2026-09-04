# Minori-kun Marketplace

Minori-kun is a Japanese agricultural marketplace implemented as one Laravel API and three independent Vue applications in a pnpm monorepo.

## Repository layout

- `apps/api` — Laravel JSON API and domain implementation.
- `apps/buyer-web` — Buyer-facing Vue application.
- `apps/producer-web` — Producer Portal Vue application.
- `apps/admin-web` — Admin Portal Vue application.
- `packages/ui` — shared UI primitives and design tokens.
- `packages/api-client` — Axios client and generated API contract types.
- `packages/config` — shared frontend tooling configuration.
- `docs` — requirements, manager workbook, architecture decisions, and project context.

## Prerequisite

Install and start Docker Desktop. Host PHP, Node.js, pnpm, MySQL, and Mailpit installations are not required. PowerShell 5.1 or newer is the supported command shell.

## First setup

From any PowerShell directory, run the script by path:

```powershell
C:\EC\m.ps1 bootstrap
```

`bootstrap` builds the images, installs locked Composer and pnpm dependencies, initializes Laravel, runs additive migrations, loads repeatable local fixtures, starts the services, and waits for health checks. It is safe to run again when recovering the development environment; it does not recreate the database.

## Daily development

```powershell
.\m.ps1 up
.\m.ps1 status
.\m.ps1 logs
.\m.ps1 stop
```

`up` starts the existing environment only. It never installs dependencies, runs migrations, seeds fixtures, or resets data.

Local URLs:

| Service | URL |
|---|---|
| API | http://localhost:8000 |
| Buyer | http://localhost:5173 |
| Producer | http://localhost:5174 |
| Admin | http://localhost:5175 |
| Mailpit | http://localhost:8025 |

Vite hot reload runs for all three SPAs from one frontend container. Source code stays on the Windows filesystem at `C:\EC`; deleting or recreating containers does not delete source code.

## Common commands

```powershell
.\m.ps1 restart
.\m.ps1 logs api
.\m.ps1 shell
.\m.ps1 artisan route:list
.\m.ps1 migrate
.\m.ps1 seed
.\m.ps1 worker
.\m.ps1 test
.\m.ps1 check
.\m.ps1 api-sync
.\m.ps1 help
```

- `seed` refreshes deterministic local fixtures without duplicating them.
- `worker` runs the database queue worker in the foreground; press `Ctrl+C` to stop it. The daily stack does not run a worker automatically.
- `test` runs Pest and frontend unit tests.
- `check` runs non-mutating formatting checks, PHPStan/Larastan, Pest, ESLint, Prettier, TypeScript, Vitest, production frontend builds, and generated-contract freshness checks.
- `api-sync` regenerates `apps/api/openapi.json` and the committed TypeScript declarations after an intentional API contract change.

## Local email

Laravel sends local SMTP mail to Mailpit. Open http://localhost:8025 to inspect messages. Mailpit is a development tool and is not a production email provider.

## Authentication and runtime storage

Sanctum uses first-party encrypted session cookies. Local sessions, cache entries, and queued jobs are stored in MySQL. No authentication token belongs in browser storage. Redis and Horizon are not part of this local stack.

Database timestamps remain UTC. Business dates and UI display use `BUSINESS_TIMEZONE=Asia/Tokyo`.

## Local demo accounts

The repeatable local seeder creates:

| Portal | Email | Password |
|---|---|---|
| Buyer | `buyer@example.test` | `password` |
| Producer | `producer@example.test` | `password` |

No default Admin credential is seeded. Create or rotate Admin access through the protected Artisan command when that workflow is implemented.

## Migrations and data safety

Run pending additive migrations with:

```powershell
.\m.ps1 migrate
```

Normal `stop`, `up`, and container rebuilds preserve MySQL data and Laravel uploads. To intentionally erase and recreate the local database:

```powershell
.\m.ps1 db-reset
```

The reset requires typing the exact confirmation phrase shown by the script. Do not use `docker compose down -v` unless all Compose-managed database and upload volumes are intentionally disposable.

## API contract workflow

Laravel Form Requests and Resources generate OpenAPI through Scramble. `openapi-typescript` generates the shared declarations used by all Vue applications.

1. Intentionally change the API contract.
2. Run `.\m.ps1 api-sync`.
3. Review both generated files.
4. Run `.\m.ps1 check`.

Pages call endpoint functions from `packages/api-client`; they do not call raw Axios directly.

## Development guidelines

- Read the relevant `FR-*`, `SCR-*`, business/data/integration/security rules, and acceptance-test IDs in `docs/REQUIREMENTS.md` before implementing a feature.
- Keep Laravel controllers thin. Use Form Requests for validation, Policies for authorization, domain actions/services for behavior, and API Resources for responses.
- Protect every API endpoint server-side by role and resource ownership. Frontend route guards and hidden buttons are UX controls, not authorization.
- Keep payment, refund, screening, fulfillment, and payout states separate. Financial and inventory writes spanning multiple records require database transactions and idempotency.
- Dispatch queued side effects only after the authoritative database transaction commits.
- Keep role-specific pages and workflows inside their SPA. `packages/ui` contains only shared primitives/tokens, and `packages/api-client` owns HTTP endpoint functions and generated types.
- Use Vue Query for server state and Pinia only for client/presentation state. Never store session secrets in Pinia, `localStorage`, or `sessionStorage`.
- Store JPY amounts as integers and percentages as basis points. Producer-funded option discounts and the Company's fixed 10% commission are separate values.
- Add focused backend authorization/invalid-state tests and frontend type/component tests with every implementation batch.
- Never commit credentials, real card/bank data, `vendor`, `node_modules`, build output, screenshots, logs, or machine-specific MCP configuration.

See `AGENTS.md` for the complete repository rules and required handoff format.

## Troubleshooting

- **Docker unavailable:** start Docker Desktop and wait until its engine is running, then retry.
- **Port already in use:** change the matching port in the root `.env.example` values copied to your local root `.env`, then restart.
- **Service not healthy:** run `.\m.ps1 status`, followed by `.\m.ps1 logs <service>`.
- **Dependencies or generated files are stale:** run `.\m.ps1 bootstrap`; use `.\m.ps1 api-sync` only for intentional API contract changes.
- **Pending database schema:** run `.\m.ps1 migrate`. Daily `up` intentionally does not migrate automatically.

## Sources of truth

- Product and system requirements: `docs/REQUIREMENTS.md`
- Manager workbook: `docs/みのりくん_EC画面要件定義書.xlsx`
- Current implementation context: `docs/PROJECT_CONTEXT.md`
- Repository agent rules: `AGENTS.md`
