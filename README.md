# ERP System

Modular monolith ERP — Laravel 13 API + Nuxt 4 SPA, fully Dockerised.

See [claude/CLAUDE.md](claude/CLAUDE.md) for architectural rules and [docs/PROJECT_PLAN.md](docs/PROJECT_PLAN.md) for the long-form plan.

## Repository layout

```
.
├── backend/          # Laravel 13 (API-only)
├── frontend/         # Nuxt 4 SPA
├── docker/           # Dockerfiles + nginx config
├── docs/             # Project docs, ADRs, conventions
├── docker-compose.yml
└── .env.example      # single source of env for the whole stack
```

## Quickstart

Prereqs on host: Docker Desktop, Node 22+, pnpm 10+, Composer 2 (only if you want to run backend tools outside Docker; Node 22+ is required for ESLint 10 on the host).

```powershell
# 1. Copy env
Copy-Item .env.example .env

# 2. Start the stack
docker compose up -d --build

# 3. First-time backend bootstrap (runs inside the php container)
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
docker compose exec php php artisan storage:link

# 4. First-time frontend bootstrap (runs inside the node container)
docker compose exec node pnpm install
```

Once up:

| Service       | URL                                     |
| ------------- | --------------------------------------- |
| Nuxt SPA      | http://localhost:3000                   |
| API (nginx)   | http://localhost:8080/api/v1            |
| Health check  | http://localhost:8080/api/v1/health     |
| Horizon       | http://localhost:8081/horizon           |
| MinIO console | http://localhost:9001                   |
| Meilisearch   | http://localhost:7700                   |
| Postgres      | localhost:5432 (erp / erp_secret_change_me) |

## Common commands

```powershell
# Backend
docker compose exec php php artisan test
docker compose exec php ./vendor/bin/pint
docker compose exec php ./vendor/bin/phpstan analyse

# Frontend
docker compose exec node pnpm lint
docker compose exec node pnpm typecheck
docker compose exec node pnpm test
docker compose exec node pnpm test:e2e
```

## Conventions

- **API envelope** — every JSON response uses the success/error envelope (see [docs/API_CONVENTIONS.md](docs/API_CONVENTIONS.md)).
- **Database** — see [docs/DB_CONVENTIONS.md](docs/DB_CONVENTIONS.md).
- **Decisions** — non-obvious choices land as ADRs under [docs/decisions/](docs/decisions/).
