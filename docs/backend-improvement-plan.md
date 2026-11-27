# План доработок backend

Составлен 2026-11-27 по итогам построчного разбора backend (`docs/backend-walkthrough.md`). Каждый пункт ниже проверен вживую: запросами к работающему API, в `tinker` или в `psql`. Статус пункта отмечается прямо здесь: `- [ ]` → `- [x]`.

---

## Для агента: начни здесь

### Контекст

ToDoTest — учебный full-stack проект: Laravel 13 / PHP 8.4, Nuxt 3, PostgreSQL 18, всё в Docker Compose. Пользователь раньше писал frontend, сейчас тимлид. На этом проекте он прокачивает **backend**: ему важно не только получить исправление, но и понять, *что* было не так и *почему* решение именно такое.

Backend готов: публичный API и админка, 180 Pest-тестов. Frontend — только каркас. Перед доработками весь backend разобран файл за файлом. Чек-лист разбора, проверенные факты и полный бэклог — в `docs/backend-walkthrough.md`, раздел «Заметки по ходу» особенно полезен.

### Прочитать перед работой

1. `CLAUDE.md` — архитектура и обязательные сопутствующие изменения.
2. `.claude/rules/backend.md` — конвенции кода, шаблон `api-changes`, SchemaSpy.
3. Этот файл целиком.
4. По необходимости — `docs/backend-walkthrough.md` (доказательства и контекст к пунктам) и `backend/docs/api.md` (контракт).

### Как работать с пользователем

- Отвечать по-русски, простым языком. Термин — с определением при первом упоминании. Объяснять на конкретных данных (Анна, Пётр, id=8), а не абстрактно.
- **Один пункт за раз:** объяснить проблему → предложить решение (если вариантов несколько — с рекомендацией) → реализовать → проверить → коротко отчитаться → дождаться подтверждения.
- Проверка пункта: `make lint`, `make test` и живой запрос (`curl` / `tinker`), который показывает «было → стало».
- Тесты к исправлениям писать и чинить по правилам проекта. Но **не разбирать их построчно** с пользователем: он решил не изучать тесты сейчас.
- Коммит и push — **только по команде пользователя**, сразу в `main`, без веток и PR. Сообщения в стиле conventional commits (`fix:`, `refactor:`, `docs:`, `ci:`).
- `backend/.env` правит сам пользователь. Чтение `.env*` запрещено настройками разрешений, и это ожидаемо.
- Пункты этапа 3 («Решения») — **сначала спросить** пользователя, потом делать.
- Изменился JSON-контракт (поле, код ошибки, заголовок, параметр запроса) → новый файл `backend/docs/api-changes/{YYYY-MM-DD}-{slug}.md` + правка `backend/docs/api.md` + типы во `frontend/types/` (по `.claude/rules/backend.md`). Изменилась схема БД → синхронизировать `backend/storage/schemaspy/schemaspy.sql`.
- После каждого пункта отметить его здесь `[x]`.

### Окружение

- `make up` — поднять. Backend: `http://localhost:8000`, frontend: `:3000`, Postgres: `:5434` (пользователь `todo`, пароль `todo`, база `todo_test`).
- Админ: `admin@example.test` / `change-me-before-production` (из `compose.yml`).
- Тесты идут на SQLite `:memory:` (`backend/phpunit.xml`), рабочая база — Postgres.
- Демо-данные в локальной базе, созданные при разборе:
  - пользователи: 1 «Разбор Демо», 2 «Заблокированный Демо» (заблокирован), 3 «Анна Смирнова», 4 «Пётр Иванов»;
  - списки: у Анны 8 «Работа и учёба» и 9 «Личные дела» (в архиве), у Петра 10 «Дача»;
  - теги: у Анны 6 «Срочно» и 7 «Работа», у Петра 8 «Срочно» и 9 «Магазин»;
  - задачи: в списке 8 — 12, 17, 21–24 (разные статусы, приоритеты, сроки и теги), в списке 9 — 26 «Старая идея», у Петра — 13.

**Вход админа из shell** (cookie и CSRF):
```bash
J=/tmp/admin.jar; B=http://localhost:8000/api/admin
xsrf() { python3 -c "import sys,urllib.parse;[print(urllib.parse.unquote(l.split()[-1])) for l in open(sys.argv[1]) if 'XSRF-TOKEN' in l]" $J; }
curl -s -c $J -b $J $B/csrf-cookie -o /dev/null
curl -s -c $J -b $J -X POST $B/login -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H "X-XSRF-TOKEN: $(xsrf)" -d '{"email":"admin@example.test","password":"change-me-before-production"}'
curl -s -b $J $B/tasks -H 'Accept: application/json'
```

**Подводные камни окружения** (на них уже наступали):
- shell — zsh: адрес с `?` брать в кавычки, иначе zsh примет его за шаблон файлов; `$VAR` с несколькими аргументами не разбивается на слова;
- кириллицу в query-параметрах URL-кодировать, иначе curl не отправит запрос (`HTTP 000`);
- `tinker` сам даёт моделям короткие имена, поэтому `User::class` там равно `'User'`. Для `::class` писать полное имя `App\Models\User`;
- `php artisan test --filter=UserApiTest` ищет подстроку и захватит `AdminUserApiTest`. Один файл: `php artisan test tests/Feature/UserApiTest.php`.

---

## Этап 1. CI на GitHub Actions

Решение пользователя от 2026-11-27: проект живёт только на GitHub, CI переносим туда. Делать первым: все следующие правки будут проверяться автоматически.

- [ ] **C1. Перенести CI из GitLab в GitHub Actions**
  - **Проблема.** `origin` — `github.com/itdzuj1/ToDoTest`, а пайплайн описан только в `.gitlab-ci.yml`; `.github/workflows` нет. При push не запускается ни одна проверка, хотя `PROJECT_HANDOFF.md` требует lint, анализ, тесты и сборку в CI. Вдобавок образ `composer:2` из старого пайплайна сейчас на PHP **8.5.13**, а Docker-образ backend — на **8.4.26**.
  - **Решение.** Workflow `.github/workflows/ci.yml` на push и pull_request:
    - backend: PHP **8.4** (расширения `mbstring`, `pdo_sqlite`, `pdo_pgsql`), кэш composer, `composer install`, затем `composer format:test`, `composer analyse`, `composer test`;
    - frontend: Node **24.18** (как в `frontend/.nvmrc` и `docs/dependencies.md`), кэш npm, `npm ci`, затем `npm run lint`, `npm run format:check`, `npm run typecheck`, `npm run test:run`, `npm run build`.

    Версии actions и setup-php проверить по официальным источникам на момент работы. Удалить `.gitlab-ci.yml`. Обновить упоминание CI в `docs/frontend-implementation-plan.md` (раздел 15). e2e и Postgres в CI — отдельные решения, см. D8.
  - **Проверка.** Push → зелёный запуск в GitHub Actions (`gh run list`, `gh run view`).

---

## Этап 2. Баги — ломают поведение или контракт

- [ ] **B1. `ApiExceptionRenderer`: неверный код 403, потерянные заголовки, 500 вне формата**
  - **Проблема.**
    1. `Illuminate\Foundation\Exceptions\Handler::render()` вызывает `prepareException()` **до** наших render-callback'ов и подменяет исключения: `AuthorizationException` → `AccessDeniedHttpException`, `ModelNotFoundException` → `NotFoundHttpException`, `TokenMismatchException` → `HttpException(419)`. Поэтому ветки `backend/app/Support/ApiExceptionRenderer.php:25-27` никогда не срабатывают. 404 и 419 получают правильный `code` случайно, через `httpError()`, а для 403 там ветки нет. В итоге **каждый 403** отдаёт `{"code":"http_error"}` вместо обещанного в `api.md` `forbidden`. Проверено на `POST /api/users/2/todo-lists` от заблокированного.
    2. Рендерер собирает новый `response()->json(...)` и теряет `$exception->getHeaders()`. 429 на входе админа приходит без `Retry-After` и `X-RateLimit-*`, 405 — без `Allow`.
    3. Неизвестное исключение (`default => null`, строка 30) уходит в стандартный JSON Laravel: при `APP_DEBUG=true` — `{message, exception, file, line, trace}`, без debug — `{"message":"Server Error"}`. Нет `code`.
  - **Решение.** Маппинг по HTTP-статусу (добавить `403 => forbidden`), мёртвые ветки убрать. Для `HttpExceptionInterface` переносить заголовки в ответ. Для неизвестных исключений отдавать `{code: "server_error", message: ...}` (500); логирование Laravel при этом сохраняется. Добавить `Retry-After` в `exposed_headers` в `backend/config/cors.php:9`: без этого браузер не даст фронту на `:3000` прочитать заголовок, даже если сервер его пришлёт.
  - **Контракт.** Меняется: `code` у 403, новые заголовки, новый код `server_error`. Нужны `api-changes`, `api.md`, `frontend/types` (если там перечислены коды).
  - **Проверка.** Тесты, которые проверяют **`code` в теле**, а не только статус: 401, 403, 404, 405, 419, 422, 429, а у 429 — `Retry-After`. Сейчас тело проверяется только для 401 и 429, поэтому баг и не пойман. Плюс живой `curl` на 403 и 429.

- [ ] **B2. `list_archived=true` → 422, а фронт отправит именно `"true"`**
  - **Проблема.** Правило `boolean` (`backend/app/Http/Requests/Admin/ListAllTasksRequest.php:47`) принимает `true/false/1/0/"1"/"0"`, но не строки `"true"`/`"false"`, а в query всё приходит строками. Фронт описывает параметр как `list_archived?: boolean` (`frontend/services/api/admin.ts:36`), а `buildQuery` делает `String(value)` (`frontend/services/api/client.ts:115`). Итог: `?list_archived=true`, и чип «Архивные списки» в админке всегда получит 422. `api.md` пишет «boolean; `true` — только архивные» и этим подталкивает к ошибке. При этом `2026-08-07-admin-tasks-index.md` верно велит слать `=1`.
  - **Решение (выбрать с пользователем).**
    - (а) backend принимает и `true`/`false`, и `1`/`0`: нормализация в `prepareForValidation()` или `Rule::in([...])` + аксессор через `$this->boolean()`. Это надёжно для любого клиента. **Рекомендация.**
    - (б) frontend сериализует boolean как `1`/`0` в `buildQuery`.

    В обоих случаях поправить формулировку в `api.md`.
  - **Контракт.** При (а) параметр расширяется, это не breaking, но нужен `api-changes`.
  - **Проверка.** `?list_archived=true` и `?list_archived=1` дают одно и то же; тест на оба варианта.

- [ ] **B3. Списки, созданные в одну секунду, приходят в случайном порядке**
  - **Проблема.** `backend/app/Http/Controllers/TodoListController.php:20` сортирует `latest()` (`ORDER BY created_at DESC`) без второго ключа, а `created_at` хранится с точностью до секунды (`timestamp(0)`). Вживую «Личное» (id=9) создан позже «Работа и учёба» (id=8), но пришёл вторым. У пользователей и задач второй ключ по `id` есть.
  - **Решение.** `->latest()->orderByDesc('id')`.
  - **Проверка.** Тест: два списка с одинаковым `created_at` → новый (больший id) первым.

- [ ] **B4. Набор полей в ответе задачи зависит от того, как политика проверила права**
  - **Проблема.** Публичный `GET /api/users/{u}/todo-lists/{l}/tasks/{t}` отдаёт лишние `is_list_archived` и `user_id`, а список задач и ответ `PATCH` той же задачи — нет. Причина: `TaskPolicy::view()` (`backend/app/Policies/TaskPolicy.php:32`) обращается к `$task->todoList` и тем самым подгружает связь, а `TaskResource` (`backend/app/Http/Resources/TaskResource.php:27`) добавляет эти поля, если связь загружена. Документ `2026-08-07-admin-tasks-index.md` прямо утверждает, что публичные эндпоинты этих полей не отдают.
  - **Решение.** Эти поля нужны только админке (там их явно грузит `with(['tags', 'todoList.user'])`). Сделать так, чтобы политика не подгружала связь как побочный эффект (проверять владельца без загрузки `todoList`, например запросом). Связано с R3.
  - **Контракт.** Из публичного `show` уходят незадокументированные поля, нужен `api-changes`.
  - **Проверка.** Тест: публичный `show` задачи без `user_id`/`is_list_archived`; админский `GET /api/admin/tasks` — с ними.

- [ ] **B5. У модели `Task` нет значений по умолчанию**
  - **Проблема.** Значения по умолчанию `status = 'todo'` и `priority = 'medium'` живут только в базе. После `$list->tasks()->create(['title' => ...])` в объекте `status === null`, хотя в базе `'todo'`: Laravel после вставки строку не перечитывает. Любой код, который вызовет `isOverdue()` сразу после такого `create()`, упадёт на `null->isNot()`. Контроллер обходит это (`backend/app/Http/Controllers/TaskController.php:116-117`), но платит лишним запросом: `INSERT` без статуса, затем `UPDATE` из `changeStatus()`.
  - **Решение.** `protected $attributes = ['status' => 'todo', 'priority' => 'medium']` в `backend/app/Models/Task.php`. По желанию — собрать `store()` в один `INSERT`: статус и `completed_at` задать до сохранения.
  - **Проверка.** Тест/tinker: `create()` → `status` равен `TaskStatus::Todo` без `refresh()`; журнал SQL `store` — без лишнего `UPDATE`.

---

## Этап 3. Решения — сначала спросить пользователя

Каждый пункт: вопрос, варианты и рекомендация. После решения он превращается в задачу и идёт по общему порядку работы.

- [ ] **D1. Заблокированные на экране выбора профиля.** `GET /api/users` их скрывает (`backend/app/Http/Controllers/UserController.php:19`), а `docs/screens.md` рисует карточку «Заблокирован — доступен только для чтения». Варианты: отдавать заблокированных в списке с `is_blocked: true` или поправить макет. **Рекомендация:** отдавать — это совпадает с доменным правилом «заблокированный доступен для чтения». Это изменение контракта.
- [ ] **D2. «Сегодня» для `due=today` / `due=upcoming`.** Сейчас границы дня по UTC. Задача со сроком 24.11 23:00 UTC (= 25.11 02:00 по Москве) попала в «сегодня» 24.11. Варианты: необязательный параметр `tz` (IANA, по умолчанию UTC) или явные границы с фронта. **Рекомендация:** `tz`.
- [ ] **D3. Сидер демо-данных.** `DatabaseSeeder` создаёт только админа, публичная часть после `make seed` пустая. **Рекомендация:** отдельный `DemoSeeder` (2–3 пользователя, активный и архивный списки, задачи с разными статусами и сроками, теги, один заблокированный), только для local.
- [ ] **D4. `UserPolicy`.** Нигде не используется (с коммита `24dd5db`), а её `before()` запрещает заблокированному даже `view`, вопреки `api.md`. **Рекомендация:** удалить.
- [ ] **D5. Поле `tasks` в `TodoListResource`.** Никогда не заполняется: задачи грузятся отдельным пагинированным запросом. **Рекомендация:** убрать вместе с `tasks?: Task[]` во `frontend/types`.
- [ ] **D6. `schemaspy.sql`.** Нигде не применяется: в живой базе комментариев нет. Варианты: make-цель, которая применяет комментарии (и, по желанию, генерирует HTML через образ SchemaSpy), или считать файл документацией. **Рекомендация:** make-цель для применения комментариев.
- [ ] **D7. CHECK-ограничения для `tasks.status` / `tasks.priority`.** Сейчас база примет `'whatever'`, и после этого любое чтение задачи падает с `ValueError` в enum-cast. **Рекомендация:** добавить новой миграцией и синхронизировать `schemaspy.sql`. Хороший учебный пункт.
- [ ] **D8. Тесты на SQLite или Postgres.** Расхождения уже видны: в SQLite `LIKE` без учёта регистра работает только для латиницы, а коды ошибок БД разные (`23000` vs `23505`). Варианты: оставить SQLite локально и добавить Postgres-джобу в CI, или перейти на Postgres полностью. Отдельно — нужен ли e2e в CI.
- [ ] **D9. `final` для моделей.** Правило проекта требует `final`-классы, модели сейчас не `final`. **Рекомендация:** зафиксировать в `.claude/rules/backend.md` исключение для Eloquent-моделей или сделать `final` — решить и записать.

---

## Этап 4. Рефакторинг дублирования

Самая учебная часть. Контракт API меняться не должен: после каждого пункта все тесты зелёные без правок ожиданий.

- [ ] **R1. Фильтры и сортировка задач — одна реализация.** Сейчас две копии: `TaskController::index` (публичный) и `Admin/TaskController::applyTaskFilters()` (`backend/app/Http/Controllers/Admin/TaskController.php:83`), с TODO-комментариями пользователя «заменить на скоупы + when». Правило «просрочена» записано в **четырёх** местах: `Task::isOverdue()`, оба контроллера и `DashboardController::show`. Решение: скоупы модели (`scopeOverdue`, `scopeSearch` и т. п.) + `when()`; убрать 8 позиционных параметров `applyTaskFilters()`.
- [ ] **R2. Шесть FormRequest'ов задач.** `StoreTaskRequest`, `UpdateTaskRequest`, `ListTasksRequest` и их админские версии плюс `ListAllTasksRequest` — около 700 строк. Различаются только `authorize()` и источником «чей тег» (`routeUserId()` / `todoListOwnerId()` / `taskOwnerId()`). Общее тело `update()` двух `TaskController` — туда же.
- [ ] **R3. Политики.** `before()` с одинаковым смыслом в `TodoListPolicy` (через `match`), `TagPolicy` и `TaskPolicy` (через `in_array`) → общий trait. В `TaskPolicy` правило «список не архивный» записано двумя способами: `isArchived()` в `create` и `whereNull('archived_at')`-запрос в `ownerHasActiveList()`. Свести к одному. Учесть B4.
- [ ] **R4. Enum.** Методы `is` / `isNot` / `inArray` скопированы в `TaskStatus` и `TaskPriority` → trait.
- [ ] **R5. Строки → enum в параметрах запросов.** `due`, `sort`, `direction`, `filter` (списки и пользователи) сейчас строки — против правила «типы параметров — enum».
- [ ] **R6. Нормализация имени** (squish + lower) повторяется в `StoreUserRequest`, `StoreTagRequest`, `UpdateTagRequest`, `UserFactory`, `TagFactory` (там только lower), а поиск в `Admin/UserController.php:21` — через `mb_strtolower` → один хелпер.
- [ ] **R7. Конфликт уникальности.** Ловить `Illuminate\Database\UniqueConstraintViolationException` вместо ручной проверки SQLSTATE `['23000', '23505']` (`backend/app/Http/Controllers/UserController.php:36`, `backend/app/Http/Controllers/TagController.php:81`). Проверено: Postgres бросает именно его. А `23000` в SQLite означает любое нарушение целостности, не только дубль.

---

## Этап 5. Защита от тихих ошибок и дыры в тестах

- [ ] **S1. Молчаливый `fill()`.** Поля не из `#[Fillable]` отбрасываются без ошибки (`$user->fill(['blocked_at' => now()])` ничего не делает). Включить `Model::preventSilentlyDiscardingAttributes()` хотя бы для local/testing — правило проекта «никаких тихих ошибок».
- [ ] **S2. Внешние ключи в `#[Fillable]`.** `user_id` у `TodoList` и `Tag`, `todo_list_id` у `Task` (`backend/app/Models/Task.php:21`) не нужны: записи создаются только через связь, а она ставит ключ сама. Фабрики белый список обходят. В `update` списка от подмены `user_id` сейчас спасает только `validated()`.
- [ ] **S3. «Молчаливый 0» в счётчиках.** `(int) ($this->todo_lists_count ?? 0)` (`backend/app/Http/Resources/UserResource.php:22`, то же в `TodoListResource`): если забыть `withCount`/`loadCount`, клиент получит правдоподобный 0 вместо ошибки.
- [ ] **S4. Спецсимволы `LIKE` в поиске.** `%` и `_` из запроса работают как шаблоны: `?search=_` вернул все задачи. SQL-инъекции нет, но поиск ведёт себя неожиданно. Экранировать.
- [ ] **S5. Дыры в тестах.**
  - Если убрать `is_string` из `StoreUserRequest::prepareForValidation()`, тесты остаются зелёными, а `POST {"name": 123}` создаёт пользователя «123». Добавить в датасет `'numeric name' => [['name' => 123]]`.
  - Ветка `catch` для гонки в `UserController::store` не покрыта.
  - `tests/Unit` — заглушка; чистая логика (enum, `isOverdue`) проверяется только Feature-тестами с базой.

---

## Этап 6. Стиль и мусор — одной пачкой

- [ ] `whereHas` → `whereRelation` (`backend/app/Http/Controllers/Admin/TaskController.php:35`, `:37`).
- [ ] `DashboardController`: константа `RecentChangesLimit` → `RECENT_CHANGES_LIMIT` (PSR-1); поле `sort_at` — копия `updated_at`, сортировать прямо по `updated_at` и убрать финальный `map`. По желанию — собрать `recent_changes` одним SQL через `UNION ALL`.
- [ ] `ListTodoListsRequest::filter()` (`backend/app/Http/Requests/ListTodoListsRequest.php:33`) читать через `validated()`, как остальные.
- [ ] В миграции `0001_01_01_000000_create_users_table` closure без `: void` — привести к стилю доменной миграции.
- [ ] Лишний `Hash::make` в `backend/database/seeders/AdminSeeder.php:27`: cast `hashed` у модели и так хэширует.
- [ ] `backend/routes/console.php` — неиспользуемая команда `inspire` из шаблона.
- [ ] Неиспользуемые подключения в `backend/config/database.php` (`mysql`, `mariadb`, `sqlsrv`, `redis`).
- [ ] Смешанные типы времени: состояния — `timestamptz`, а `created_at`/`updated_at` — `timestamp` без пояса. Рассмотреть `timestampsTz()`. Это миграция → `schemaspy.sql`.
- [ ] Индекс `task_tag(tag_id)` — на вырост: составной PK его не покрывает.
- [ ] Предупреждение eslint `vue/html-self-closing` в `frontend/components/ui/UiInput.vue:19` (`npx eslint --fix`).

---

## Что известно, но не требует правки

- Админ-эндпоинты проверяют права дважды: middleware `auth:admin` и `authorize()` в FormRequest. Это страховка, оставить.
- Целостность «тег и задача одного владельца» в `task_tag` держит только валидация `tag_ids.*`. Внешним ключом такое не выразить — валидацию не обходить.
- `AdminSeeder` (`firstOrCreate`) не обновит пароль, если поменять `ADMIN_PASSWORD` в `compose.yml`. Сидер создаёт админа, но не управляет им.
- В тестах CSRF не проверяется вообще (`PreventRequestForgery::runningUnitTests()`), только вживую.
