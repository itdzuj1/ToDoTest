.PHONY: up down build logs backend-shell frontend-shell migrate lint test check

up:
	docker compose up -d --build

down:
	docker compose down

build:
	docker compose build

logs:
	docker compose logs -f

backend-shell:
	docker compose exec backend sh

frontend-shell:
	docker compose exec frontend sh

migrate:
	docker compose exec backend php artisan migrate

lint:
	docker compose exec -T frontend npm run lint
	docker compose exec -T backend composer format:test
	docker compose exec -T backend composer analyse

test:
	docker compose exec -T frontend npm run test:run
	docker compose exec -T backend composer test

check: lint test
	docker compose exec -T frontend npm run typecheck
	docker compose exec -T frontend npm run build

