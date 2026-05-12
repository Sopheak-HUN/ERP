# API Conventions

> Stub — flesh out as conventions become concrete. The scaffolding in
> `backend/app/Support/ApiResponse.php` and `backend/bootstrap/app.php` is the
> source of truth for the envelope.

## Versioning

- All routes prefixed `/api/v1/`.
- Breaking changes go in a new prefix (`/api/v2/`); old versions stay until deprecated.

## Response envelope

### Success

```json
{
  "success": true,
  "data": { },
  "message": "Optional human-readable message",
  "meta": { "pagination": { } }
}
```

### Error

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Human-readable error",
    "details": { }
  }
}
```

### Error codes

| Code | HTTP | When |
| --- | --- | --- |
| `VALIDATION_ERROR` | 422 | Request fails Form Request validation |
| `UNAUTHENTICATED` | 401 | Sanctum / `auth:sanctum` rejects |
| `FORBIDDEN` | 403 | Policy denies |
| `NOT_FOUND` | 404 | Route or model missing |
| `HTTP_ERROR` | 4xx | Other Symfony HTTP exceptions |
| `SERVER_ERROR` | 500 | Unhandled exceptions |

Module-specific codes go in `app/Modules/{Module}/Exceptions/` and extend
`App\Exceptions\ApiException`.

## Request headers

| Header | Direction | Notes |
| --- | --- | --- |
| `Accept: application/json` | Required | Triggers JSON renderer in the exception handler |
| `Content-Type: application/json` | Required for write methods | |
| `Authorization: Bearer <token>` | Required when not stateful | Sanctum personal access token |
| `X-Request-Id` | Optional | Client-supplied ULID; server echoes (and replaces if not a ULID) |
| `Idempotency-Key` | Required for financial/inventory writes | Per CLAUDE.md §10 |

## Pagination

- Default page size: `25`, max: `100`.
- Query: `?page=2&per_page=50`.
- Response `meta.pagination`: `{ total, per_page, current_page, last_page, from, to }`.

## Authorization

- Sanctum tokens (SPA cookie or personal access).
- Every endpoint that touches a model uses a Policy.
- Routes never call `Gate::allows` inline — controllers use `$this->authorize(...)`.

## Naming

- Routes: kebab-case (`/api/v1/sales-orders`).
- Query params: snake_case.
- JSON fields: snake_case (matches DB columns + Eloquent default).

## OpenAPI docs

Scribe generates docs from controller PHPDoc and route metadata. Build with:

```
php artisan scribe:generate
```
