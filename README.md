# ToDoTest

Учебный full-stack todo-проект с переключаемыми демо-профилями и защищённой административной зоной.

## Стек

- Nuxt 3.21, Vue 3, TypeScript
- Laravel 13, PHP 8.4
- PostgreSQL 18
- Docker Compose

## Требования

- Docker Desktop с поддержкой Docker Compose
- Make (необязательно, команды можно запускать напрямую через `docker compose`)

Локальные Node.js, PHP и PostgreSQL не требуются.

## Запуск

1. Создайте локальную конфигурацию backend:

   ```bash
   cp backend/.env.example backend/.env
   ```

2. Соберите и запустите сервисы:

   ```bash
   make up
   ```

3. Создайте ключ приложения и выполните миграции:

   ```bash
   docker compose exec backend php artisan key:generate
   make migrate
   ```

Frontend доступен на `http://localhost:3000`, backend — на `http://localhost:8000`.

## Проверки

```bash
make lint
make test
make check
```

Команда `make check` запускает форматирование в режиме проверки, статический анализ, тесты, TypeScript typecheck и production build frontend.

## Структура

- `frontend/` — единое Nuxt-приложение для публичной части и `/admin`
- `backend/` — Laravel API
- `docker/` — образы локальной разработки
- `docs/` — документация проекта и API

