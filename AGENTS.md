# Repository Agent Instructions

## Authority and Sources

- Treat `docs/REQUIREMENTS.md` as the product and system requirement source of truth.
- Use this precedence when sources conflict:
  1. Explicit management confirmations recorded in the requirements.
  2. The user's latest explicit correction or decision.
  3. `docs/PROJECT_CONTEXT.md`.
  4. The current Figma `V2` page only as a visual reference; never use it to invent business rules.
  5. Archived spreadsheets, prototypes, Penpot artifacts, and earlier session output.
- Do not silently resolve a conflict. Record unresolved conflicts in the Open Decision Registry and stop work that depends on them.

## Requirement Status Rules

- `[CONFIRMED]` is binding. Do not change it without a new explicit decision from the user or management.
- `[PROPOSED]` is the documented default for planning, not an approved business decision. Keep it reversible and identify it in handoffs.
- `[TBD]` is unresolved. Never implement one option as though it were confirmed.
- `[OUT-OF-SCOPE]` must not be added to the current release.
- `[LATER]` may be considered only when the user explicitly expands the release scope.
- Reference requirement IDs in plans, implementation notes, tests, and reviews.

## Non-Negotiable Domain Rules

- The system is a marketplace with Buyer, Producer, and Admin roles.
- Producers are the sellers; the company operates the platform and receives a 10% sales commission.
- Producer-funded customer discounts are authoritative per sellable product option and are separate from the Company's fixed 10% commission. Never combine the calculations or labels.
- Buyers must never see internal producer settlement or company commission data.
- Producers may access only their own products, order items, sales, bank account, and payouts.
- Payment card numbers, security codes, and raw payment credentials must never be stored by this system.
- A browser return from PAY.JP is not proof of payment or screening success. Use the authoritative API or verified webhook state.
- Financial state changes and sensitive administrative actions require an audit trail.

## Language and UI Text

- Write requirements, implementation documentation, and agent handoffs in English.
- Preserve exact Japanese UI labels where specified. When discussing a Japanese label outside the UI specification, include its English meaning.
- The customer-facing locale is Japanese, the currency is JPY, and the business timezone is Asia/Tokyo.

## Repository Architecture

- Use a single monorepo with one Laravel modular-monolith backend and three independent Vue applications.
- `apps/api` contains the complete standard Laravel project, including `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`, and `tests`.
- `apps/buyer-web`, `apps/producer-web`, and `apps/admin-web` contain the Buyer, Producer, and Admin Vue applications respectively.
- `packages/ui` contains shared UI primitives and design tokens only. Role-specific pages, layouts, navigation, and workflows remain inside their application.
- `packages/api-contracts` contains generated backend contract types only. Do not place Axios instances, request helpers, endpoint functions, or workflows in shared packages.
- `packages/config` contains shared frontend tooling configuration.
- Keep Laravel Composer dependencies under `apps/api/vendor` and JavaScript dependencies in workspace-managed `node_modules`; never commit either directory.

## Backend Implementation Rules

- Follow Laravel conventions and keep controllers thin: validate with Form Requests, authorize with Policies, delegate business behavior to domain actions/services, and serialize with API Resources.
- Organize core behavior by domain, including Identity, Catalog, Orders, Payments, Fulfillment, Producer Onboarding, Settlements, Administration, and Audit.
- Use server-side role and object-ownership authorization for every protected endpoint; hiding UI controls is not authorization.
- Wrap multi-record financial and inventory changes in database transactions.
- Process provider callbacks, payments, refunds, screening, payouts, and repeated commands idempotently.
- Dispatch asynchronous side effects only after the authoritative transaction commits. This rule does not require every email or side effect to use a queue. Follow the feature's documented delivery behavior; when introducing a queue, document its worker requirement and operational impact. Producer password reset and Producer registration send synchronously after commit. Password reset must retain a neutral request response even when mail delivery fails; log only a safe failure event and allow the user to request a new link.
- Keep order, payment, refund, fulfillment, screening, and payout states separate and validate every state transition.
- Version public application endpoints under `/api/v1`; webhook routes use their own authenticated/verified boundary.

## Frontend Implementation Rules

- Use Vue 3, TypeScript, Vite, Vue Router, Pinia, Tailwind CSS, and shadcn-vue unless the user explicitly changes the approved stack.
- Use shadcn-vue source components as owned, customizable primitives; do not use React shadcn/ui packages in Vue applications.
- Build application screens with shared shadcn-vue primitives and Tailwind utility classes. Do not add page-level custom CSS, scoped style blocks, or replacement raw HTML form controls when a shared primitive exists. Keep global CSS limited to framework imports, base rules, and design tokens.
- Prefer existing Tailwind utilities and theme scales across Buyer, Producer, and Admin. For typography, use standard font-size classes such as `text-xs`, `text-sm`, `text-base`, `text-lg`, `text-xl`, and `text-2xl` instead of hardcoded arbitrary sizes such as `text-[24px]`; use `text-2xl` for the equivalent default size. Use standard line-height utilities where suitable. Only introduce a custom value when an explicit requirement cannot be met by the existing scale; define reusable typography values as shared design tokens rather than repeating arbitrary values in pages.
- Choose typography independently for each screen by content role, hierarchy, readability, and supported viewport; do not treat another application's existing typography as authoritative. Use responsive Tailwind font-size classes where needed and verify long Japanese text at mobile widths. Converting `px` to a scale class alone does not reduce the rendered size; review oversized text explicitly. Honor confirmed screen requirements when they specify a different size.
- Define form validation with Zod schemas and show accessible field-level errors through shared form primitives. Keep a schema used by one page inside that page, matching the existing Producer page pattern. Extract schemas only when implemented consumers require reuse; keep reused schemas within their feature, not in the generic `src/lib` folder. Keep page-specific timers and lifecycle behavior inside the page rather than introducing generic helpers for a single consumer. Preserve the exact password value; do not trim it or apply registration password-composition rules to login.
- Prefer Lucide icons from `lucide-vue-next`, as used by shadcn-vue, for interface icons. Reuse an existing Lucide icon whenever suitable instead of drawing custom SVG icons, using emoji or text glyphs as icons, or adding another icon library. Keep icon sizes and stroke widths consistent; hide decorative icons from assistive technology and give icon-only controls accessible names. Brand logos and decorative illustrations are separate from interface icons.
- Import shared primitives and tokens from `packages/ui`; do not place business workflows or role-specific page components there.
- Each frontend owns one `src/services/api.ts` for its Axios instance, base URL, cookie/CSRF configuration, and interceptors only. It must not contain feature endpoint functions or error-display helpers.
- Put endpoint calls directly in feature `services/<feature>/<feature>.query.ts` and `<feature>.mutation.ts` files. Keep cache keys in `<feature>.key.ts`. Do not add `<feature>.api.ts` wrappers or centralize endpoint functions in `services/api.ts`. Create only files needed by implemented behavior.
- Import generated types from `@minorikun/api-contracts`; keep app-specific type aliases in `src/types` and error helpers in `src/lib`. Pages and components consume query/mutation services without raw endpoint calls. Preserve Sanctum cookie/CSRF authentication.
- Keep the current implementation scope in Producer unless the user requests Buyer/Admin changes. Buyer now has its own Axios client and uses `@minorikun/api-contracts`; this does not establish that every Buyer feature follows the final service layout. Admin still references the removed `@minorikun/api-client` package and requires migration before workspace installation or its build can succeed. Do not expand a Producer task to repair Admin.
- Buyer UI is mobile-first. Producer and Admin portals are desktop-oriented and must remain usable at the supported viewport sizes.
- Implement loading, empty, validation, error, retry, disabled, and unavailable states required by the relevant screen specification.
- Communicate status with text or icon plus text, never color alone.

## Change Boundaries

- Do not mutate Figma, Figwright, Penpot, external services, or payment systems unless the user explicitly requests that mutation.
- When replacing a Figma design, never place the replacement on top of the superseded frame. Edit the original in place, or build the replacement beside it; after visual verification, delete the exact superseded node before moving the replacement into its final position, then verify that no overlapping duplicate frames remain.
- Do not generate or edit the Excel requirement workbook while the Markdown requirement is being established unless the user explicitly starts the Excel phase.
- Treat only `docs/みのりくん_EC画面要件定義書.xlsx` as the current manager workbook; archived workbooks are historical evidence only.
- Preserve unrelated user files and existing artifacts. Never delete or replace them merely because they are outdated.
- Update existing documentation in place. Do not create new documentation, implementation notes, or handoff files unless the user explicitly requests a new document.
- Make small, reviewable changes and verify the affected requirement IDs after each batch.
- Do not install or upgrade packages unless the current task requires it. When adding a package, explain its purpose and pin versions through the appropriate lockfile.
- Do not add generated screenshots, session transcripts, temporary scripts, machine-specific MCP configuration, logs, or build output to the application repository.

## Development and Verification Workflow

- Before implementing a screen or behavior, read its `FR-*`, `SCR-*`, detailed screen requirements, related `BR-*`/`DATA-*`/`INT-*`/`SEC-*`, and acceptance test IDs.
- Use short-lived feature branches and keep changes scoped to one coherent requirement group.
- Backend changes require focused unit/feature tests, including authorization and invalid-state cases.
- Frontend changes require the affected application's type checking and build, plus focused lint and manual verification where applicable. Do not create frontend automated test files, test scripts, or a new test harness unless the user explicitly requests them. Existing frontend checks may be run when available. Backend automated tests remain required.
- Payment and financial changes must test duplicate, replayed, delayed, mismatched, out-of-order, and partially failed behavior.
- Never use real credentials, card data, bank data, or unnecessary personal information in source, fixtures, logs, screenshots, or tests.
- Run the smallest relevant checks during iteration and the affected application test/build suite before handoff.

## Required Handoff

Every completed task must report:

- Requirement IDs addressed.
- Files changed.
- Checks performed and their results.
- Remaining `[TBD]` or `[PROPOSED]` dependencies.
- Any behavior intentionally left out of scope.

