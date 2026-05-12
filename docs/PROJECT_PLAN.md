# ERP System — Project Plan

> Canonical roadmap. Referenced from `claude/CLAUDE.md`. Update this document
> when scope, modules, or slices change. All implementation work must trace
> back to a slice defined here.

**Last updated:** 2026-05-12
**Bootstrap session:** 2026-05-12 — foundation only, no business modules.

---

## 1. Vision and Scope

### Vision
Build an enterprise-grade ERP web application that small-to-mid-sized
organizations can self-host, covering the core operational backbone:
HR, inventory, procurement, sales, and accounting — with a clean modular
architecture that can grow into manufacturing, projects, and advanced
analytics over time.

### In Scope (v1)
- Web application only (responsive, desktop-first)
- Single-tenant deployment, multi-tenant-ready schema
- Multi-user with role-based access control
- Multi-currency-ready (single base currency in v1)
- English UI with i18n infrastructure for future languages
- Self-hosted via Docker
- Open-source dependencies only

### Out of Scope (v1)
- Native mobile apps (iOS/Android)
- Microservices architecture (modular monolith only)
- Multi-tenant SaaS hosting layer
- Real-time collaborative editing
- Advanced BI / data warehouse
- Workflow designer UI (workflows are code-defined)
- Plugin or marketplace system
- Manufacturing module
- Project management module
- Integrations with third-party systems (banks, tax authorities, e-commerce)
- Commercial packages (none permitted at any stage)

---

## 2. Stakeholders and Timeline

### Stakeholders
- **Owner / Product Lead:** [your name]
- **Engineering:** Claude Code as primary implementer, owner as reviewer
- **End users (target):** Operations, HR, Finance, Sales, and Procurement
  staff at small-to-mid-sized organizations

### Indicative Timeline
Estimates assume one focused implementer working steadily. Adjust based on
actual cadence.

| Milestone | Duration | Cumulative |
|---|---|---|
| MVP Foundation (Modules 1–4) | 2–3 months | Month 3 |
| Operations Core (Modules 6–8) | 3–4 months | Month 7 |
| Financial Core (Module 9) | 3–4 months | Month 11 |
| HR & Payroll (Module 5) | 2–3 months | Month 14 |
| Reporting & Analytics (Module 10) | 1–2 months | Month 16 |
| Hardening, polish, docs | Ongoing | — |

---

## 3. Technology Stack (Summary)

Full details live in `claude/CLAUDE.md`. Summary:

- **Backend:** Laravel 13 API, PHP 8.3+, PostgreSQL 16, Redis 7+, Sanctum,
  spatie/permission, Meilisearch, MinIO, Horizon, Reverb, Pest, Pint,
  Larastan.
- **Frontend:** Nuxt 4, Tailwind v4, Nuxt UI, Pinia, VeeValidate + Zod,
  TanStack Table, Chart.js, Lucide, @nuxtjs/i18n, Vitest, Playwright.
- **Infrastructure:** Docker Compose, Nginx, GitHub Actions.

---

## 4. Architecture Summary

- Modular monolith with HMVC-style module folders under `app/Modules/`
- API-first; frontend is a separate Nuxt 4 SPA
- Layered: Controller → FormRequest → Service → Repository → Model
- Event-driven cross-module communication
- API versioned under `/api/v1/`
- Standard response and error envelopes
- Soft deletes, audit trail, and `tenant_id` on every business table

Full architectural rules and coding standards are authoritative in
`claude/CLAUDE.md`.

---

## 5. Module Implementation Order

Modules must be built in this order. Do not start a later module until the
prior module's MVP slices are complete and tested.

1. Core Infrastructure
2. Authentication & Authorization
3. User & Organization Management
4. Master Data Management
5. HR & Payroll
6. Inventory Management
7. Procurement
8. Sales & CRM
9. Accounting & Finance
10. Reporting & Analytics

---

## 6. Module-by-Module Slice Plan

### MODULE 1 — Core Infrastructure
**Goal:** Establish the foundation everything depends on.

**Phase 1.1 — Project Bootstrap**
- 1.1.1 Repository setup, branching strategy, `.editorconfig`
- 1.1.2 Docker Compose environment (PHP, Nginx, PostgreSQL, Redis, MinIO,
  Meilisearch, Nuxt)
- 1.1.3 Laravel 13 installation, base config, environment files
- 1.1.4 Nuxt 4 installation, Tailwind v4 setup, base config
- 1.1.5 CI pipeline (lint, test, build) for both apps

**Phase 1.2 — API Foundation**
- 1.2.1 API versioning strategy (`/api/v1/`)
- 1.2.2 Standard response envelope
- 1.2.3 Global exception handler with consistent error codes
- 1.2.4 Request ID middleware and correlation IDs
- 1.2.5 Rate limiting strategy
- 1.2.6 CORS configuration
- 1.2.7 Health check endpoints (`/health`, `/ready`)

**Phase 1.3 — Frontend Foundation**
- 1.3.1 API client composable with interceptors
- 1.3.2 Global error handling and toast notifications
- 1.3.3 Loading states and skeleton screen patterns
- 1.3.4 Base layout (sidebar, topbar, breadcrumbs)
- 1.3.5 Theming (light/dark), design tokens
- 1.3.6 i18n setup with English baseline

**Phase 1.4 — Cross-Cutting Concerns**
- 1.4.1 Activity log infrastructure (spatie/activitylog)
- 1.4.2 Audit trail base trait
- 1.4.3 Soft deletes pattern
- 1.4.4 File upload service (MinIO integration)
- 1.4.5 Notification infrastructure (database, mail, broadcast)
- 1.4.6 Queue and job monitoring (Horizon)

**Acceptance Criteria**
- Dev environment boots with `docker compose up` and all services healthy
- Health endpoints return 200
- A test request through the API returns the standard envelope
- A test exception returns the standard error envelope
- Frontend boots, hits the API, displays the base layout
- CI runs lint, test, build on every PR for both apps

---

### MODULE 2 — Authentication & Authorization
**Goal:** Secure access control system.

**Phase 2.1 — Authentication**
- 2.1.1 User model and migration (with multi-tenant fields)
- 2.1.2 Sanctum SPA setup
- 2.1.3 Login, logout, refresh endpoints
- 2.1.4 Password reset flow (email-based)
- 2.1.5 Email verification
- 2.1.6 Two-factor authentication (TOTP)
- 2.1.7 Login attempt throttling and lockout
- 2.1.8 Session management (list active sessions, revoke)

**Phase 2.2 — Authorization (RBAC)**
- 2.2.1 Roles and permissions models (spatie/laravel-permission)
- 2.2.2 Permission seeder with system-defined permissions
- 2.2.3 Role CRUD API
- 2.2.4 Permission assignment to roles
- 2.2.5 User-role assignment
- 2.2.6 Middleware and policy enforcement
- 2.2.7 Frontend permission directives (`v-can`)

**Phase 2.3 — Frontend Auth**
- 2.3.1 Login page and form
- 2.3.2 Auth store (Pinia) with token persistence
- 2.3.3 Route guards (auth middleware)
- 2.3.4 User menu and profile dropdown
- 2.3.5 Password reset UI
- 2.3.6 2FA setup UI

**Acceptance Criteria**
- A new user can be seeded, log in, complete 2FA, and access a protected route
- Permission checks block unauthorized API calls and UI actions
- Password reset and email verification flows succeed end-to-end
- Sessions can be listed and revoked
- All endpoints rate-limited; lockout activates after configured failures

---

### MODULE 3 — User & Organization Management
**Goal:** Manage users, departments, and company structure.

**Phase 3.1 — Company/Tenant Setup**
- 3.1.1 Company profile model (name, logo, address, tax info)
- 3.1.2 Multi-branch support
- 3.1.3 Company settings API
- 3.1.4 Frontend company settings page

**Phase 3.2 — Organizational Structure**
- 3.2.1 Department model with hierarchy
- 3.2.2 Position / job title management
- 3.2.3 Org chart visualization
- 3.2.4 Department CRUD UI

**Phase 3.3 — User Management**
- 3.3.1 User CRUD with role assignment
- 3.3.2 User profile page (self-service)
- 3.3.3 User listing with filters
- 3.3.4 Bulk user import (CSV)
- 3.3.5 User activity log viewer

**Acceptance Criteria**
- Company profile is editable and reflected in PDFs and emails
- Departments form a tree and render as an org chart
- Users can be created, assigned roles and departments, and invited
- Bulk import handles validation errors gracefully

---

### MODULE 4 — Master Data Management
**Goal:** Centralized reference data.

**Phase 4.1 — Generic Lookups**
- 4.1.1 Countries, currencies, languages
- 4.1.2 Units of measure
- 4.1.3 Tax codes and rates
- 4.1.4 Payment terms
- 4.1.5 Generic dropdown/lookup API

**Phase 4.2 — Business Partners**
- 4.2.1 Customer model and CRUD
- 4.2.2 Supplier/vendor model and CRUD
- 4.2.3 Contact persons
- 4.2.4 Address book with multiple addresses
- 4.2.5 Partner categorization and tagging

**Phase 4.3 — Product/Item Master**
- 4.3.1 Item categories (hierarchical)
- 4.3.2 Item master (SKU, name, description, UoM)
- 4.3.3 Item attributes and variants
- 4.3.4 Item pricing (cost, sell price, currency)
- 4.3.5 Item images and documents
- 4.3.6 Barcode / QR code support

**Acceptance Criteria**
- Lookups are seeded and exposed through a unified dropdown API
- Customers, suppliers, and contacts can be created and linked
- Items can be created with variants, prices, and images
- All entities are searchable via Meilisearch

---

### MODULE 5 — HR & Payroll
**Goal:** Employee lifecycle management.

**Phase 5.1 — Employee Management**
- 5.1.1 Employee model (extends user, with HR-specific fields)
- 5.1.2 Employee onboarding workflow
- 5.1.3 Employment contracts
- 5.1.4 Documents (ID, certificates) upload
- 5.1.5 Employee directory UI

**Phase 5.2 — Attendance**
- 5.2.1 Shift definitions
- 5.2.2 Attendance log (clock-in/out)
- 5.2.3 Attendance approval workflow
- 5.2.4 Attendance reports

**Phase 5.3 — Leave Management**
- 5.3.1 Leave types and policies
- 5.3.2 Leave balance tracking
- 5.3.3 Leave request and approval workflow
- 5.3.4 Leave calendar view

**Phase 5.4 — Payroll**
- 5.4.1 Salary components (earnings, deductions)
- 5.4.2 Salary structure templates
- 5.4.3 Payroll period setup
- 5.4.4 Payroll calculation engine
- 5.4.5 Payslip generation (PDF)
- 5.4.6 Statutory deductions
- 5.4.7 Payroll reports

**Acceptance Criteria**
- Employee records can be created, updated, and offboarded with audit trail
- Attendance, leave, and payroll cycles run end-to-end on seeded data
- Payslips render as PDFs with the correct company branding
- Payroll postings produce balanced journal entries in the GL

---

### MODULE 6 — Inventory Management
**Goal:** Track stock across locations.

**Phase 6.1 — Warehouse Setup**
- 6.1.1 Warehouse/location model
- 6.1.2 Storage bins/zones
- 6.1.3 Location CRUD UI

**Phase 6.2 — Stock Movements**
- 6.2.1 Stock transaction model (immutable ledger pattern)
- 6.2.2 Goods receipt (inbound)
- 6.2.3 Goods issue (outbound)
- 6.2.4 Stock transfer between warehouses
- 6.2.5 Stock adjustment with reason codes

**Phase 6.3 — Stock Valuation**
- 6.3.1 Costing methods (FIFO, weighted average)
- 6.3.2 Stock balance calculation
- 6.3.3 Stock aging report
- 6.3.4 Real-time stock dashboard

**Phase 6.4 — Stock Counting**
- 6.4.1 Physical count sheets
- 6.4.2 Cycle counting
- 6.4.3 Variance reconciliation

**Acceptance Criteria**
- Stock movements never produce negative balances except where explicitly
  allowed
- All movements post to the GL when accounting integration is active
- Valuation reports match the GL inventory account balance
- Counting reconciles variances and produces adjustment postings

---

### MODULE 7 — Procurement
**Goal:** Purchase-to-pay process.

**Phase 7.1 — Purchase Requisition**
- 7.1.1 PR model and form
- 7.1.2 PR approval workflow
- 7.1.3 PR to PO conversion

**Phase 7.2 — Purchase Orders**
- 7.2.1 PO model with line items
- 7.2.2 PO approval workflow
- 7.2.3 PO PDF generation and email
- 7.2.4 PO status tracking (sent, acknowledged, received)

**Phase 7.3 — Goods Receipt**
- 7.3.1 GRN against PO
- 7.3.2 Partial receipts handling
- 7.3.3 Quality inspection

**Phase 7.4 — Vendor Invoicing**
- 7.4.1 Vendor bill entry (3-way matching: PO, GRN, invoice)
- 7.4.2 Invoice approval
- 7.4.3 Posts to accounting (AP)

**Acceptance Criteria**
- End-to-end PR → PO → GRN → bill flow works on seeded data
- Three-way matching prevents over-billing
- All postings produce balanced AP journals

---

### MODULE 8 — Sales & CRM
**Goal:** Order-to-cash process.

**Phase 8.1 — Sales Quotations**
- 8.1.1 Quotation model with line items
- 8.1.2 Quote PDF and email
- 8.1.3 Quote to order conversion

**Phase 8.2 — Sales Orders**
- 8.2.1 SO model with line items
- 8.2.2 Credit limit check
- 8.2.3 SO approval workflow
- 8.2.4 SO status tracking

**Phase 8.3 — Delivery**
- 8.3.1 Delivery note / picking list
- 8.3.2 Partial delivery handling
- 8.3.3 Posts to inventory (goods issue)

**Phase 8.4 — Customer Invoicing**
- 8.4.1 Sales invoice from SO/delivery
- 8.4.2 Invoice PDF and email
- 8.4.3 Posts to accounting (AR)

**Phase 8.5 — CRM (Light)**
- 8.5.1 Leads management
- 8.5.2 Opportunity pipeline (kanban)
- 8.5.3 Activities (calls, meetings, notes)

**Acceptance Criteria**
- End-to-end quote → SO → delivery → invoice flow works
- Credit limits block orders that exceed thresholds
- Lead-to-opportunity pipeline tracks conversion rate

---

### MODULE 9 — Accounting & Finance
**Goal:** Financial backbone. Critical and complex.

**Phase 9.1 — Chart of Accounts**
- 9.1.1 Account types (asset, liability, equity, income, expense)
- 9.1.2 COA hierarchical model
- 9.1.3 COA import / seed for standard templates
- 9.1.4 COA management UI

**Phase 9.2 — General Ledger**
- 9.2.1 Journal entry model (double-entry enforcement)
- 9.2.2 Manual journal entries
- 9.2.3 Journal approval workflow
- 9.2.4 Fiscal year and period management
- 9.2.5 Period closing

**Phase 9.3 — Accounts Payable**
- 9.3.1 AP aging
- 9.3.2 Vendor payment processing
- 9.3.3 Payment posting to GL

**Phase 9.4 — Accounts Receivable**
- 9.4.1 AR aging
- 9.4.2 Customer payment receipt
- 9.4.3 Payment allocation to invoices

**Phase 9.5 — Banking**
- 9.5.1 Bank accounts setup
- 9.5.2 Bank transactions
- 9.5.3 Bank reconciliation

**Phase 9.6 — Financial Reports**
- 9.6.1 Trial balance
- 9.6.2 Income statement (P&L)
- 9.6.3 Balance sheet
- 9.6.4 Cash flow statement
- 9.6.5 General ledger report
- 9.6.6 Custom report builder

**Acceptance Criteria**
- Every journal entry balances; database constraint enforces this
- Closed periods reject new postings
- Trial balance ties to GL totals; P&L and balance sheet tie to trial balance
- AP, AR, and bank reconciliations all complete on seeded data

---

### MODULE 10 — Reporting & Analytics
**Goal:** Cross-module insights.

**Phase 10.1 — Dashboards**
- 10.1.1 Role-based dashboard framework
- 10.1.2 Widget library (KPI cards, charts, tables)
- 10.1.3 Executive dashboard
- 10.1.4 Departmental dashboards

**Phase 10.2 — Reporting Engine**
- 10.2.1 Report definition model
- 10.2.2 Filter and parameter framework
- 10.2.3 Export to PDF, Excel, CSV
- 10.2.4 Scheduled reports (email delivery)

**Acceptance Criteria**
- Dashboards render under one second on seeded data
- Reports export to all three formats and arrive via email on schedule
- Permissions restrict report visibility by role

---

## 7. Cross-Cutting Considerations

### Plan Early
- Database design for shared entities (companies, users, partners, items)
- Document numbering schemes (`PO-2026-0001`), configurable per company
- Multi-currency design
- Generic approval workflow engine, reused across modules
- Polymorphic document attachment system
- Customizable print templates per company
- Notification system (in-app, email)

### Defer
- Microservices migration
- Mobile apps
- Advanced BI / data warehouse
- Workflow designer UI
- Plugin / extension system
- Multi-language for master data
- Third-party integrations

---

## 8. Risks and Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Accounting integrity bugs | Severe | Enforce double-entry at DB level; wrap all postings in transactions; full Pest coverage on GL |
| Concurrency on stock / GL | High | Optimistic locking with `version` column; row-level locks on critical writes |
| Tax compliance drift | Medium | Isolate tax logic in a service; defer country-specific rules to a later phase |
| Performance degradation | High | Index strategy from day one; paginate everything; cache hot reads in Redis |
| Data migration from legacy | Medium | Build generic import tooling early in Module 4 |
| Backup and recovery gaps | Severe | Document backup/restore procedure as part of Module 1; test restore quarterly |
| Audit trail gaps | High | Apply spatie/activitylog from Module 1 onwards; never skip on critical models |
| Scope creep | Medium | Anything not in this plan goes through an ADR before implementation |
| Dependency abandonment | Medium | Prefer well-maintained packages; document fallbacks in ADRs |
| Single implementer bottleneck | Medium | Keep slices small; keep docs current; commit early and often |

---

## 9. Definition of Done (per slice)

A slice is done when:
- All checklist items in `claude/CLAUDE.md` (Slice Workflow) are satisfied
- Code review (or self-review against this plan) is complete
- `PROGRESS.md` is updated with the slice checked off
- Conventional commit is merged to the main branch
- Any deviation from this plan is captured in `/docs/decisions/` as an ADR

---

## 10. Change Log

Significant changes to this plan must be appended here with date and reason.

- **2026-05-12** — Initial plan drafted from project kickoff discussion.