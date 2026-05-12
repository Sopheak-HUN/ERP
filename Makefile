.PHONY: help up down restart build logs ps \
        backend frontend php node \
        be-install be-migrate be-test be-pint be-stan \
        fe-install fe-dev fe-lint fe-typecheck fe-test fe-e2e \
        clean

help:
	@echo "ERP System — common tasks"
	@echo ""
	@echo "Stack:"
	@echo "  make up              Start all containers (detached)"
	@echo "  make down            Stop all containers"
	@echo "  make restart         Restart all containers"
	@echo "  make build           Rebuild images"
	@echo "  make logs            Tail logs from all services"
	@echo "  make ps              List running containers"
	@echo ""
	@echo "Backend (Laravel):"
	@echo "  make be-install      composer install"
	@echo "  make be-migrate      Run migrations"
	@echo "  make be-test         pest"
	@echo "  make be-pint         pint --test"
	@echo "  make be-stan         phpstan analyse"
	@echo ""
	@echo "Frontend (Nuxt):"
	@echo "  make fe-install      pnpm install"
	@echo "  make fe-lint         pnpm lint"
	@echo "  make fe-typecheck    pnpm typecheck"
	@echo "  make fe-test         pnpm test (vitest)"
	@echo "  make fe-e2e          pnpm test:e2e (playwright)"
	@echo ""
	@echo "Shells:"
	@echo "  make php             Shell into php container"
	@echo "  make node            Shell into node container"

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build

logs:
	docker compose logs -f --tail=100

ps:
	docker compose ps

php:
	docker compose exec php sh

node:
	docker compose exec node sh

be-install:
	docker compose exec php composer install

be-migrate:
	docker compose exec php php artisan migrate

be-test:
	docker compose exec php ./vendor/bin/pest

be-pint:
	docker compose exec php ./vendor/bin/pint --test

be-stan:
	docker compose exec php ./vendor/bin/phpstan analyse --memory-limit=1G

fe-install:
	docker compose exec node pnpm install

fe-dev:
	docker compose exec node pnpm dev

fe-lint:
	docker compose exec node pnpm lint

fe-typecheck:
	docker compose exec node pnpm typecheck

fe-test:
	docker compose exec node pnpm test

fe-e2e:
	docker compose exec node pnpm test:e2e

clean:
	docker compose down -v
