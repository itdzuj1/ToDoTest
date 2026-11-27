# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Обзор

Учебный full-stack todo-проект: Nuxt 3 frontend (публичная зона + `/admin` в одном приложении), Laravel 13 / PHP 8.4 API, PostgreSQL 18. Всё запускается только через Docker Compose — локальные Node.js, PHP и Postgres не требуются и не предполагаются.

## Команды

Все команды выполняются на хосте; они сами проксируют вызовы в контейнеры.

```bash
make up            # docker compose up -d --build
make down
make migrate       # artisan migrate
make seed          # artisan db:seed (создаёт админа из ADMIN_* в compose.yml)
make logs

make lint          # eslint + pint --test + phpstan
make test          # vitest run + pest
make check         # lint + test + nuxt typecheck + nuxt build
```

Запуск отдельных проверок и одиночных тестов:

```bash
# Backend (Pest)
docker compose exec -T backend php artisan test --filter="creates a task"
docker compose exec -T backend vendor/bin/pest tests/Feature/TaskApiTest.php
docker compose exec -T backend composer analyse      # PHPStan level 6 (app, database, routes)
docker compose exec -T backend composer format       # Pint --write

# Frontend (Vitest / Playwright)
docker compose exec -T frontend npx vitest run tests/unit/api/errors.spec.ts
docker compose exec -T frontend npm run test:e2e -- --grep "profile"
docker compose exec -T frontend npm run typecheck
docker compose exec -T frontend npm run format       # prettier --write
```

Тесты backend идут на SQLite `:memory:` — `phpunit.xml` форсирует `DB_CONNECTION`/`DB_DATABASE` через `<env force>` и `<server>`, потому что Docker выставляет Postgres-переменные в `$_SERVER`. Не убирайте эти форсы. Pest подключает `RefreshDatabase` для всего `tests/Feature` в `tests/Pest.php`.

Frontend доступен на `http://localhost:3000`, backend — на `http://localhost:8000`, Postgres проброшен на `5434`.

## Архитектура backend

### Две зоны маршрутов с разным middleware

- `routes/api.php` — **публичный API**, полностью stateless и без аутентификации. Демо-профиль передаётся сегментом URL: `/api/users/{user}/todo-lists/{todoList}/tasks/{task}`, вложенные группы используют `scopeBindings()`.
- `routes/web.php` — **админский API** под префиксом `api/admin`. Он намеренно живёт в `web.php`, чтобы получить session- и CSRF-middleware группы `web`; guard — `auth:admin`. При добавлении админского эндпоинта правьте `routes/web.php`, а не `routes/api.php`.

Аутентификация только одна — сессионный guard `admin` (`config/auth.php` содержит **только** провайдер `admins`). Bearer-токенов, Sanctum и «пользовательского логина» в проекте нет. Флоу входа: `GET /api/admin/csrf-cookie` → `POST /api/admin/login` (throttle `admin-login`, 5/мин по хэшу email+IP) → cookie-сессия; мутирующие запросы шлют `X-XSRF-TOKEN` из cookie `XSRF-TOKEN`.

### Авторизация

Публичные контроллеры не имеют аутентифицированного пользователя, поэтому проверки идут через `Gate::forUser($user)->authorize(...)` с `$user` из route binding. Политики (`app/Policies`) используют `before(User|Admin $actor)`:

- `Admin` проходит любую проверку — админ управляет задачами независимо от `blocked` и `archived`;
- заблокированный `User` (`blocked_at !== null`) сохраняет только `viewAny`/`view`.

Доменные состояния — nullable-таймстемпы, а не флаги: `users.blocked_at`, `todo_lists.archived_at`, `tasks.completed_at`. Переходы инкапсулированы в моделях (`User::block/unblock`, `TodoList::archive/restore`, `Task::changeStatus`), они идемпотентны и используют `forceFill(...)->saveOrFail()`.

### Формат ответов и ошибок

`App\Support\ApiExceptionRenderer` зарегистрирован в `bootstrap/app.php` и превращает исключения в единый контракт для всех `api/*`:

- 422 → `{ "errors": { "field": ["..."] } }`;
- остальное → `{ "code": "...", "message": "..." }` со стабильными кодами `unauthenticated`, `forbidden`, `not_found`, `method_not_allowed`, `csrf_token_mismatch`, `too_many_requests`, `http_error`.

Внутренние детали исключений наружу не попадают. POST — создание, PATCH — обновление, DELETE — удаление/архивация по контракту; успешные create/update возвращают актуальный Resource, delete — 204. Бизнес-конфликты отдают стабильный машиночитаемый `code` и безопасный для пользователя `message`. Пагинированные эндпоинты используют одну структуру data/meta. Авторизация всегда проверяется на backend, даже если frontend скрывает недоступные действия. OpenAPI/кодогенерацию не вводить, пока контракт не стабилизировался.

Счётчики связей задаются статическим `resourceCountRelations()` на модели (`User`, `TodoList`) и подставляются в `withCount()`/`loadCount()` — добавляя счётчик в Resource, расширяйте этот метод, а не пишите `withCount` по месту.

Поток: Route → FormRequest → Controller → Model/Resource. FormRequest'ы содержат нормализацию и типизированные аксессоры (`$request->status()`, `->dueAt()`, `->tagIds()`), контроллеры остаются тонкими, многошаговые записи обёрнуты в `DB::transaction()`.

## Архитектура frontend

Одно Nuxt-приложение: `layouts/default.vue` для публичной зоны, `layouts/admin.vue` для `/admin`; `middleware/profile.ts` и `middleware/admin-auth.ts` разграничивают доступ. **Сейчас оба middleware — заглушки**, реальная логика приходит вместе со слайсами `useDemoProfile()` / `useAdminSession()`.

`services/api/` — единственное место, где известны пути эндпоинтов; страницы и компоненты вызывают только `api.profiles`, `api.todoLists`, `api.tasks`, `api.tags`, `api.admin.*`.

- `client.ts`: `apiRequest` (сырой ответ, 204 → `undefined`) и `apiData` (разворачивает конверт `{ data }` и падает явной ошибкой, если конверта нет). Все запросы идут с `credentials: 'include'`; при `csrf: true` сначала запрашивается CSRF-cookie.
- `errors.ts`: класс `ApiError` c `status`/`code`/`fieldErrors` и геттерами `isValidationError`, `isForbidden`, `isCsrfMismatch`; 422 маппится в ошибки полей.

Слои компонентов (префикс определяет уровень знаний): `Ui*` — примитивы без домена и API, `El*` — composed-элементы форм, `Pt*` — паттерны, знающие доменные типы, но не эндпоинты, `Tm*` — крупные блоки/layouts/sheets. Файлы PascalCase, теги в шаблонах kebab-case. Порядок в SFC: `template` → `script setup lang="ts"` → `style scoped lang="scss"`.

Стили — только SCSS + CSS custom properties из `assets/styles/tokens/`. Tailwind, utility-CSS, raw hex, сырые px для spacing/radius и статические inline-стили запрещены. Тема меняется переопределением переменных в `assets/styles/themes/`.

Nuxt работает в strict TS с `typeCheck: true`, поэтому ошибка типов ломает и `dev`, и `build`.

## Обязательные сопутствующие изменения

- **Меняете JSON-контракт** (Resource, FormRequest, routes, типы полей) — создайте `backend/docs/api-changes/{YYYY-MM-DD}-{slug}.md` по шаблону соседних файлов (затронутые эндпоинты, таблица было/стало, breaking changes, migration notes) и обновите `backend/docs/api.md` и типы во `frontend/types/`.
- **Пишете миграцию, меняющую таблицы/колонки** — синхронизируйте `backend/storage/schemaspy/schemaspy.sql` (`COMMENT ON TABLE` / `COMMENT ON COLUMN`, описания на русском).

## Конвенции кода (сверх стандартных)

Backend:

- arrow functions (`fn () => ...`) для замыканий; обычный `function` — только если тело не выражение;
- `whereRelation` вместо `whereHas`, если хватает возможностей;
- backed enum передаётся в query builder как case, без `->value`; сравнение — через `->is()` / `->isNot()` / `->inArray()`, а не `===`;
- типы параметров — enum, не `int`/`string`; конвертация из HTTP один раз на границе (FormRequest);
- `final` классы, promoted readonly-зависимости, явные типы возврата, анонимные миграции.

Общее: kebab-case в URL, snake_case в JSON, camelCase в TypeScript. Никаких «тихих» ошибок и пустых `catch` — любая неудача видима. Проект маленький: сервис, composable или абстракция добавляются только под реальную переиспользуемую ответственность.

## Дополнительные источники

- `docs/` — концепция, экраны, состояния, компоненты, план реализации frontend и дизайн-хендофф (`docs/frontend-implementation-plan.md`, `docs/components.md`, `docs/states.md`).
- `backend/docs/api.md` — полный API-контракт.
- `docs/backend-improvement-plan.md` — **текущая работа**: план доработок backend по этапам, статусы пунктов, рабочие договорённости с пользователем, состояние локального окружения. Начинать сессию с него.
- `docs/backend-walkthrough.md` — итоги построчного разбора backend: чек-лист, исходный бэклог находок, проверенные факты о поведении кода («Заметки по ходу»).
- `.claude/rules/` — детальные правила, подгружаются автоматически по `paths` при работе с соответствующими файлами: `backend.md` (Laravel, enum, api-changes, schemaspy), `frontend.md` (структура, именование, API layer, слайсы, definition of done), `frontend-styles.md` (токены, типографика, SCSS), `frontend-components.md` (контракты паттернов, состояния, a11y), `frontend-testing.md`. При расхождении с этим файлом первоисточник — они.
