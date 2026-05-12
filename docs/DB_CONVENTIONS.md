# Database Conventions

> Stub — flesh out as schema patterns emerge. The migrations under
> `backend/database/migrations/` are the source of truth for what's actually
> deployed.

## Engine

- PostgreSQL 16.
- Schema: `public` (multi-schema split deferred until extraction to services).

## Naming

| Object | Convention | Example |
| --- | --- | --- |
| Tables | plural snake_case | `sales_orders`, `partners` |
| Columns | snake_case | `created_at`, `tenant_id` |
| Primary key | `id` (bigint, auto-increment) | |
| Foreign key | `<singular_table>_id` | `partner_id`, `warehouse_id` |
| Unique index | `<table>_<col>_unique` | `users_email_unique` |
| Plain index | `<table>_<col>_index` | `sales_orders_partner_id_index` |
| Pivot | both tables, alphabetical | `role_user` |

## Standard columns on every business table

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigserial | PK |
| `tenant_id` | bigint (nullable) | Multi-tenant ready; nullable until tenancy ships |
| `created_at` | timestamptz | Laravel default |
| `updated_at` | timestamptz | Laravel default |
| `deleted_at` | timestamptz (nullable) | Soft delete |
| `created_by` | bigint (nullable) | FK to `users.id` |
| `updated_by` | bigint (nullable) | FK to `users.id` |
| `deleted_by` | bigint (nullable) | FK to `users.id` |
| `version` | integer | Optimistic locking for concurrent-edit risk |

## Types

- **Money**: store as `bigint` in the smallest currency unit (e.g., USD cents).
  Never `decimal`, never `float`. Display formatting happens at the API resource
  or frontend layer.
- **Dates/times**: `timestamptz` stored in UTC; convert at presentation.
- **Enums**: prefer `varchar` + application-side PHP enum, not Postgres ENUM type
  (alterations are painful with Postgres ENUMs).
- **UUIDs / ULIDs**: use `char(26)` for ULIDs, `uuid` for UUIDs. Keep `id`
  bigserial for performance; add a separate `ulid` column when an external-facing
  identifier is needed.

## Constraints and indexes

- All FKs declared (`->constrained()`); choose `cascadeOnDelete` or
  `restrictOnDelete` deliberately, never `nullOnDelete` unless required.
- Composite indexes lead with the column used in equality, not range.
- Unique constraints for natural keys (e.g., `(tenant_id, code)` on partners).

## Migrations

- One change per migration; never edit a merged migration.
- `down()` must be reversible — no destructive `dropTable` without a
  matching `up()` recreation, unless intentional.
- Heavy data migrations live in a separate seeder/command, not in the
  schema migration itself.

## Accounting & financial tables (preview)

- Journal entries must always balance — enforced both in the service layer and
  via a Postgres trigger on the journal lines table.
- Reference: CLAUDE.md §13.
