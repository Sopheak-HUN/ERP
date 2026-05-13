# Implementation Progress

> Living checklist of ERP slices. Tick a box when the slice meets the
> Definition of Done in [PROJECT_PLAN.md §9](PROJECT_PLAN.md#9-definition-of-done-per-slice).
> All slices for **Module 2** are tracked below. Other modules will be added
> as they begin.

**Legend:** `[ ]` not started · `[~]` in progress · `[x]` done · `[-]` deferred / out of scope

---

## Module 1 — Core Infrastructure

### Phase 1.4 — Cross-Cutting Concerns

- [x] 1.4.1 Activity log infrastructure (spatie/activitylog)
- [x] 1.4.2 Audit trail base trait
- [x] 1.4.3 Soft deletes pattern
- [x] 1.4.4 File upload service (MinIO integration)
- [x] 1.4.5 Notification infrastructure (database, mail, broadcast)
- [x] 1.4.6 Queue and job monitoring (Horizon)

## Module 2 — Authentication & Authorization

### Phase 2.1 — Authentication (8 slices)

- [x] 2.1.1 User model and migration (with multi-tenant fields)
- [x] 2.1.2 Sanctum SPA setup
- [x] 2.1.3 Login, logout, refresh endpoints
- [x] 2.1.4 Password reset flow (email-based)
- [x] 2.1.5 Email verification
- [x] 2.1.6 Two-factor authentication (TOTP)
- [x] 2.1.7 Login attempt throttling and lockout
- [x] 2.1.8 Session management (list active sessions, revoke)

### Phase 2.2 — Authorization / RBAC (7 slices)

- [x] 2.2.1 Roles and permissions models (spatie/laravel-permission)
- [x] 2.2.2 Permission seeder with system-defined permissions
- [x] 2.2.3 Role CRUD API
- [x] 2.2.4 Permission assignment to roles
- [x] 2.2.5 User-role assignment
- [x] 2.2.6 Middleware and policy enforcement
- [x] 2.2.7 Frontend permission directives (`v-can`)

### Phase 2.3 — Frontend Auth (6 slices)

- [x] 2.3.1 Login page and form
- [x] 2.3.2 Auth store (Pinia) with token persistence
- [x] 2.3.3 Route guards (auth middleware)
- [x] 2.3.4 User menu and profile dropdown
- [x] 2.3.5 Password reset UI
- [x] 2.3.6 2FA setup UI

---

## Module 3 — User & Organization Management

### Phase 3.1 — Company/Tenant Setup (4 slices)

- [x] 3.1.1 Company profile model (name, logo, address, tax info)
- [x] 3.1.2 Multi-branch support
- [x] 3.1.3 Company settings API
- [x] 3.1.4 Frontend company settings page

## Notes

- Module 1 (Core Infrastructure) is largely scaffolded in `/backend` and
  `/frontend`: Laravel 13 + Sanctum + Horizon + Spatie permission + activitylog,
  `ApiResponse` envelope, exception renderer, `RequestId` middleware, health
  endpoint, `/api/v1/` versioned routes. A retrospective Module 1 checklist
  can be added if we want a formal sign-off before starting 2.1.1.
- Updates to this file should accompany the same PR that completes the slice.
