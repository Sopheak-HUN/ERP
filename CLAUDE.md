# ERP System - Project Context for Claude Code

## Project Overview
Enterprise-grade ERP web application with a clear separation between API backend and SPA frontend. This is a **modular monolith** that may extract to microservices later. All packages used MUST be open-source (no commercial/paid packages).

## Technology Stack

### Backend (`/backend`)
- **Framework**: Laravel 13 (API-only, no Blade views)
- **PHP**: 8.3+
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis 7+
- **Auth**: Laravel Sanctum (SPA token auth)
- **RBAC**: spatie/laravel-permission
- **Search**: Meilisearch
- **Storage**: MinIO (S3-compatible)
- **Queue Dashboard**: Laravel Horizon
- **Activity Log**: spatie/laravel-activitylog
- **Excel**: maatwebsite/excel
- **PDF**: Generated on the **frontend** via Puppeteer (Nuxt). The backend
  serves data + a print-styled HTML view; the PDF binary is produced client-side.
  `barryvdh/laravel-dompdf` is **not** used.
- **WebSocket**: Laravel Reverb
- **Testing**: Pest PHP
- **Linting**: Laravel Pint + Larastan (level 8)

### Frontend (`/frontend`)
- **Framework**: Nuxt 4 (SSR disabled for app pages, SSR enabled for public)
- **CSS**: Tailwind CSS v4
- **UI Library**: Nuxt UI (open source)
- **State**: Pinia
- **Validation**: VeeValidate + Zod
- **Tables**: TanStack Table (Vue)
- **Charts**: Chart.js + vue-chartjs
- **Icons**: Lucide Vue
- **i18n**: @nuxtjs/i18n
- **Testing**: Vitest + Playwright

### Infrastructure
- Docker + Docker Compose for local dev
- Nginx as reverse proxy
- GitHub Actions for CI/CD

## Project Structure
/
├── backend/                    # Laravel 13 API
│   ├── app/
│   │   ├── Modules/           # Domain modules (HMVC-style)
│   │   │   └── {Module}/
│   │   │       ├── Controllers/
│   │   │       ├── Models/
│   │   │       ├── Services/
│   │   │       ├── Repositories/
│   │   │       ├── Requests/
│   │   │       ├── Resources/
│   │   │       ├── Policies/
│   │   │       ├── Events/
│   │   │       ├── Listeners/
│   │   │       ├── Jobs/
│   │   │       ├── Routes/
│   │   │       └── Database/
│   │   │           ├── Migrations/
│   │   │           ├── Seeders/
│   │   │           └── Factories/
│   │   ├── Core/              # Shared kernel (base classes)
│   │   └── Support/           # Helpers, traits
│   ├── config/
│   ├── routes/
│   └── tests/
├── frontend/                   # Nuxt 4 SPA
│   ├── app/
│   │   ├── components/        # Shared UI components
│   │   ├── composables/       # useApi, useAuth, etc.
│   │   ├── layouts/
│   │   ├── middleware/
│   │   ├── pages/
│   │   ├── stores/            # Pinia stores
│   │   ├── modules/           # Feature modules
│   │   │   └── {module}/
│   │   │       ├── components/
│   │   │       ├── composables/
│   │   │       ├── pages/
│   │   │       └── types/
│   │   └── utils/
│   └── server/
└── docker/
├── nginx/
├── php/
└── docker-compose.yml
## Architectural Principles (MUST follow)

1. **API-First**: Backend is pure JSON API. Never use Blade for app UI.
2. **Multi-tenancy ready**: Every business table includes `tenant_id` (nullable initially) and `created_by`, `updated_by`, `deleted_by`.
3. **Soft deletes**: All business entities use soft deletes.
4. **Audit trail**: Use spatie/activitylog on all critical models.
5. **Layered architecture**: Controller → FormRequest → Service → Repository → Model. Controllers MUST stay thin (no business logic).
6. **Event-driven**: Cross-module communication uses Laravel Events, never direct service calls between modules.
7. **API versioning**: All routes prefixed `/api/v1/`.
8. **Response envelope**: Standard response format:
```json
   {
     "success": true,
     "data": {...},
     "message": "...",
     "meta": { "pagination": {...} }
   }
```
9. **Error envelope**:
```json
   {
     "success": false,
     "error": {
       "code": "VALIDATION_ERROR",
       "message": "...",
       "details": [...]
     }
   }
```
10. **Idempotency**: Financial/inventory write endpoints accept `Idempotency-Key` header.
11. **Optimistic locking**: Use `version` column on entities with concurrent edit risk.
12. **Database transactions**: All multi-step writes wrapped in transactions.
13. **Double-entry accounting**: Accounting journals must always balance — enforced at DB and application level.

## Coding Standards

### Backend
- PSR-12 compliance (enforced by Pint)
- Strict types: `declare(strict_types=1);` at top of every PHP file
- All public methods must have return types
- Use Eloquent relationships, not manual joins, unless performance demands
- Use Form Request classes for all input validation — no inline validation in controllers
- Use API Resources for all responses — never return raw models
- Service classes are stateless; inject dependencies via constructor
- Use enums (PHP 8.1+) for fixed value sets (status, types)
- All money values stored as integers (smallest currency unit, e.g., cents) — never floats
- All dates stored as UTC; convert at presentation layer
- Naming: PascalCase classes, camelCase methods, snake_case DB columns
- Migrations are immutable once merged; create new migrations to alter
- Larastan must pass at level 8 with zero errors

### Frontend
- TypeScript strict mode — no `any` unless absolutely necessary
- Composition API only (no Options API)
- `<script setup lang="ts">` for all components
- Components: PascalCase filenames, multi-word names
- Composables prefixed with `use` (e.g., `useEmployees`)
- Pinia stores: one store per module domain
- All API calls go through composables, never directly in components
- Tailwind: use design tokens via CSS variables; avoid arbitrary values when possible
- Accessibility: semantic HTML, ARIA labels, keyboard navigation support
- All forms use VeeValidate + Zod schemas
- Error handling: every async call has try/catch with user-friendly toast

### Git Commit Convention
Conventional Commits:
- `feat(module): add employee CRUD`
- `fix(auth): correct token refresh logic`
- `refactor(inventory): extract stock service`
- `test(payroll): add salary calc tests`
- `docs(api): update authentication guide`
- `chore(deps): bump laravel to 13.2`

## Module Implementation Order

Build modules in this strict order — do not start a later module until the earlier one has its MVP slices complete:

1. **Core Infrastructure** — Docker, base app shells, response envelopes, error handling
2. **Authentication & Authorization** — Sanctum, RBAC, login flow
3. **User & Organization Management** — Company, departments, users
4. **Master Data Management** — Lookups, partners (customers/suppliers), items
5. **HR & Payroll** — Employees, attendance, leave, payroll
6. **Inventory Management** — Warehouses, stock movements, valuation
7. **Procurement** — PR, PO, GRN, vendor invoicing
8. **Sales & CRM** — Quotations, orders, delivery, invoicing
9. **Accounting & Finance** — COA, GL, AP, AR, banking, reports
10. **Reporting & Analytics** — Dashboards, report engine

## Slice Workflow (How to Build)

Every slice must follow this checklist before being considered done:

### Backend Slice
- [ ] Migration written (with proper indexes, foreign keys)
- [ ] Model with relationships, casts, fillable
- [ ] Factory and seeder
- [ ] Service class with business logic
- [ ] Form Request(s) for validation
- [ ] API Resource for response shape
- [ ] Controller (thin)
- [ ] Routes registered in module's route file
- [ ] Policy for authorization
- [ ] Feature tests (Pest) covering happy path + key edge cases
- [ ] Pint passes, Larastan passes at level 8
- [ ] Updated OpenAPI docs (Scribe annotations)

### Frontend Slice
- [ ] Pinia store (if state needed)
- [ ] Composable for API calls
- [ ] TypeScript types matching API resource
- [ ] List page with TanStack Table, pagination, search, filters
- [ ] Detail page
- [ ] Create/Edit form with VeeValidate + Zod
- [ ] Delete confirmation
- [ ] Permission checks on actions
- [ ] Loading and error states
- [ ] Empty states
- [ ] Unit tests (Vitest) for composables and stores
- [ ] E2E happy path test (Playwright)

## Things to NEVER Do

- Never use commercial packages (Spark, Nova, Forge, Vapor, paid Tailwind UI, etc.)
- Never put business logic in controllers
- Never return Eloquent models directly from API
- Never use floats for money
- Never make synchronous cross-module service calls — use events
- Never skip migrations to edit existing ones (after merge)
- Never commit `.env` files or secrets
- Never use `any` type in TypeScript without comment justification
- Never use inline styles when Tailwind classes can do the job
- Never disable a test to make CI pass — fix the test or the code
- Never auto-update dependencies without testing

## Things to ALWAYS Do

- Always run `php artisan test`, `pint`, and `phpstan` before considering backend work done
- Always run `pnpm test`, `pnpm lint`, and `pnpm typecheck` before considering frontend work done
- Always update `CHANGELOG.md` for user-visible changes
- Always document non-obvious decisions in `/docs/decisions/` as ADRs
- Always use database transactions for multi-table writes
- Always paginate list endpoints (default 25, max 100)
- Always include `created_at`, `updated_at` in responses
- Always rate-limit public endpoints
- Always sanitize user input and escape output

## How to Ask for Help

When I (the developer) give you a task:
1. Confirm which **module → phase → slice** the task belongs to
2. List the files you plan to create/modify before writing code
3. Flag any deviation from this CLAUDE.md or any ambiguity
4. Implement the slice end-to-end (migration → API → frontend) unless told to do backend or frontend only
5. Run tests and linters; report results
6. Summarize what was done and what's next

## Reference Documents
- Full project plan: `/docs/PROJECT_PLAN.md`
- API conventions: `/docs/API_CONVENTIONS.md`
- Database conventions: `/docs/DB_CONVENTIONS.md`
- ADRs: `/docs/decisions/`