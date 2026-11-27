# Разбор backend: чек-лист повторения

Последовательный проход по готовому backend: каждая сущность, каждый файл.
Найденные проблемы здесь не чиним, а записываем в «Бэклог улучшений» — доработки идут отдельным этапом после разбора.

## Формат шага

1. Построчный разбор файлов шага.
2. Уточняющие вопросы.
3. 3–5 контрольных вопросов на самопроверку.
4. Живые curl-запросы (включая «плохие» 404/403/422) и эксперименты в `tinker`. Тестовые файлы (`tests/...`) не разбираем — решение от 2026-11-23.
5. Отметить шаг `[x]`, дописать бэклог и открытые вопросы.

Внутри сущности порядок «снизу вверх»: модель (+enum, factory) → policy → маршруты → FormRequest → контроллер → Resource → тесты → документация API.

Пути ниже указаны относительно `backend/`, если не сказано иное.

## Чек-лист

### Блок 0. Карта проекта и жизненный цикл запроса

- [x] Инфраструктура: `../compose.yml`, `../docker/backend/Dockerfile`, `../Makefile`, `composer.json` (scripts)
- [x] Вход запроса: `public/index.php`, `bootstrap/app.php`, `bootstrap/providers.php`, `routes/api.php` / `routes/web.php` (обзор разделения), `routes/console.php`
- [x] Ошибки: `app/Support/ApiExceptionRenderer.php`
- [x] Конфиги: `config/database.php`, `config/cors.php`, `config/session.php` (только значимые места)
- [x] Тестовая обвязка и инструменты: `phpunit.xml`, `tests/Pest.php`, `tests/TestCase.php`, `tests/Feature/ExampleTest.php`, `pint.json`, `phpstan.neon`

### Блок 1. Схема данных

- [x] `database/migrations/0001_01_01_000000_create_users_table.php`, `0001_01_01_000001_create_cache_table.php`
- [x] `database/migrations/2026_07_26_120000_create_todo_domain_tables.php`
- [x] `storage/schemaspy/schemaspy.sql`, `docs/api-changes/2026-07-26-backend-domain-foundation.md`

### Блок 2. User — демо-профиль

- [x] Модель: `app/Models/User.php`, `database/factories/UserFactory.php`
- [x] Права: `app/Policies/UserPolicy.php`
- [x] HTTP: маршруты `users.*`, `app/Http/Requests/StoreUserRequest.php`, `app/Http/Controllers/UserController.php`, `app/Http/Resources/UserResource.php`
- [x] Тесты и контракт: `tests/Feature/UserApiTest.php`, «Демо-профили» в `docs/api.md`, `docs/api-changes/2026-07-30-demo-profiles.md`

### Блок 3. TodoList

- [x] Модель: `app/Models/TodoList.php`, `database/factories/TodoListFactory.php`
- [x] Права: `app/Policies/TodoListPolicy.php`
- [x] HTTP: группа `users/{user}/todo-lists`, `ListTodoListsRequest`, `StoreTodoListRequest`, `UpdateTodoListRequest`, `TodoListController.php`, `TodoListResource.php`
- [x] Контракт: `docs/api-changes/2026-07-29-todo-list-lifecycle.md`

### Блок 4. Tag

- [x] Модель и права: `app/Models/Tag.php`, `database/factories/TagFactory.php`, `app/Policies/TagPolicy.php`
- [x] HTTP: группа `users/{user}/tags`, `StoreTagRequest`, `UpdateTagRequest`, `TagController.php`, `TagResource.php`
- [x] Контракт: «Теги» в `docs/api.md`

### Блок 5. Task

- [x] 5.1 Домен: `app/Enums/TaskStatus.php`, `app/Enums/TaskPriority.php`, `app/Models/Task.php`, `database/factories/TaskFactory.php`
- [x] 5.2 Права и маршруты: `app/Policies/TaskPolicy.php`, маршруты `tasks.*`
- [x] 5.3 Create / show / update / delete: `StoreTaskRequest`, `UpdateTaskRequest`, `TaskController` (store, show, update, destroy), `TaskResource`
- [x] 5.4 Список задач: `ListTasksRequest`, `TaskController::index`, «Задачи» в `docs/api.md`, `docs/api-changes/2026-08-05-tasks-tags-api.md`

### Блок 6. Админ: аутентификация

- [x] Модель и конфиг: `app/Models/Admin.php`, `database/factories/AdminFactory.php`, `config/auth.php`, `config/admin.php`, `database/seeders/AdminSeeder.php`, `database/seeders/DatabaseSeeder.php`
- [x] HTTP: `routes/web.php`, `app/Http/Controllers/Admin/AuthController.php`, `app/Http/Requests/Admin/LoginRequest.php`, `app/Http/Resources/AdminResource.php`
- [x] Защита: `app/Providers/AppServiceProvider.php` (лимит `admin-login`), `config/session.php`, `config/cors.php`
- [x] Контракт: `docs/api-changes/2026-08-05-admin-api.md` (разобран вместе с 7.3)

### Блок 7. Админ: управление

- [x] 7.1 Dashboard: `app/Http/Controllers/Admin/DashboardController.php`, `docs/api-changes/2026-08-07-resource-counts-dashboard.md`
- [x] 7.2 Пользователи и списки: `Admin/UserController.php`, `Requests/Admin/ListUsersRequest.php`, `Admin/TodoListController.php`
- [x] 7.3 Задачи: `Admin/TaskController.php`, `Requests/Admin/ListTasksRequest.php`, `Requests/Admin/ListAllTasksRequest.php`, `Requests/Admin/UpdateTaskRequest.php`, `docs/api-changes/2026-08-07-admin-tasks-index.md`

### Блок 8. Итог

- [x] `docs/api.md` целиком как контракт
- [x] backend-джобы в `../.gitlab-ci.yml`
- [x] полный прогон `make lint` и `make test` (2026-11-27: lint — 0 ошибок, 1 предупреждение во frontend; тесты — frontend 9/9, backend 180/180)
- [x] приоритизация бэклога → план доработок (раздел «Итог разбора» ниже)

## Итог разбора

Разбор backend завершён 2026-11-27: блоки 0–8 пройдены, каждый файл прочитан и проверен вживую.

Рабочий план доработок со статусами, решениями и способами проверки — **`docs/backend-improvement-plan.md`**. Бэклог ниже — исходный список находок в порядке обнаружения. Он остаётся как история и источник доказательств, а статусы ведутся в плане.

## Бэклог улучшений

- **Баг на стыке фронта и бэкенда:** `GET /api/admin/tasks?list_archived=true` → 422 `must be true or false` (правило `boolean` в Laravel принимает `1/0/"1"/"0"/true/false`, но не строки `"true"/"false"`). А фронт (`AdminTasksParams.list_archived?: boolean` + `buildQuery` → `String(value)`) отправит именно `"true"`. Документ `2026-08-07-admin-tasks-index.md` велит слать `list_archived=1`, но тип и клиент фронта этого не делают. Чип «Архивные списки» в админке всегда получит 422. Починить с одной из сторон: фронт сериализует boolean как `1/0`, или бэкенд принимает `true/false`.
- Фильтрация и сортировка задач продублированы: `TaskController::index` и `Admin/TaskController::applyTaskFilters` (там же TODO-комментарии про скоупы). Правило «просрочена» записано уже в **четырёх** местах: `Task::isOverdue()`, `TaskController::index`, `Admin/TaskController::applyTaskFilters`, `DashboardController::show` — главный кандидат на скоуп `Task::scopeOverdue()`.
- Дублируются `ListTasksRequest` ×3, `Store/UpdateTaskRequest` ×3 и тело `update` в публичном и админском `TaskController`.
- `whereHas` вместо `whereRelation` в `Admin/TaskController::indexAll`.
- Строки вместо enum для `due`, `sort`, `direction`, `filter` — расходится с правилом «типы параметров — enum».
- `UserPolicy` нигде не используется: создана в `24dd5db` и с тех пор ни разу не вызывалась. К тому же её `before()` запрещает заблокированному **даже `view`** — в отличие от остальных политик и вопреки `api.md` («заблокированный профиль доступен по прямому ID»). Подключи её кто-то в `UserController::show`, и контракт сломается. Решить: удалить или переписать в стиле остальных политик.
- Нет сидера демо-данных: `DatabaseSeeder` создаёт только админа.
- `GET /api/users` скрывает заблокированных, а `../docs/screens.md` показывает их на экране выбора профиля.
- Модели не `final`, хотя правило требует `final`-классы — решить, применимо ли оно к Eloquent-моделям.
- `routes/console.php` содержит только неиспользуемую команду `inspire` из шаблона Laravel.
- На вырост: у `task_tag` нет отдельного индекса по `tag_id` — PK `(task_id, tag_id)` его не покрывает. При удалении тега Postgres ищет связи по `tag_id` полным перебором. На учебных объёмах неважно.
- Смешанные типы времени: доменные состояния (`blocked_at`, `archived_at`, `due_at`, `completed_at`) — `timestamptz`, а `created_at`/`updated_at` из `timestamps()` — `timestamp` без пояса. Работает, потому что `app.timezone` и пояс БД — UTC. Для единообразия можно перейти на `timestampsTz()`.
- `fill()` молча отбрасывает поля не из `#[Fillable]`: `$user->fill(['blocked_at' => now()])` ничего не делает и не ругается (проверено в tinker). Это расходится с правилом «никаких тихих ошибок». Рассмотреть `Model::preventSilentlyDiscardingAttributes()` (или `Model::shouldBeStrict()`) хотя бы для local/testing.
- **Баг сортировки:** `TodoListController::index` сортирует `latest()` = `ORDER BY created_at DESC` без второго ключа. `created_at` хранится с точностью до секунды (`timestamp(0)`), поэтому списки, созданные в одну секунду, приходят в случайном порядке. Вживую: «Личное» (id=9) создан позже «Работа и учёба» (id=8), но в ответе второй. У задач и пользователей добор по `id` есть, у списков нет.
- Поле `tasks` в `TodoListResource` (`whenLoaded('tasks')`) сейчас никогда не заполняется: связь `tasks` у списка нигде не загружается (админка грузит списки пользователя без задач). Заготовка «на будущее» — оставить или убрать вместе с `tasks?: Task[]` во `frontend/types`.
- `ListTodoListsRequest::filter()` читает сырое `$this->has()/string()`, а не `validated()`, как остальные FormRequest'ы. Работает (валидация прошла раньше), но стиль разный.
- У модели `Task` нет значений по умолчанию (`$attributes`): после `create(['title' => ...])` в объекте `status = null`, хотя база записала `'todo'` (проверено в tinker). Контроллер обходит это, явно ставя `priority` и вызывая `changeStatus()`, но любой другой код, создавший задачу и сразу вызвавший `isOverdue()`, упадёт на `null->isNot()`. Добавить `protected $attributes = ['status' => 'todo', 'priority' => 'medium']`. Побочный эффект нынешней схемы: `TaskController::store` пишет задачу двумя запросами — `INSERT` без статуса, затем `UPDATE status, completed_at` из `changeStatus()` (видно в журнале SQL).
- Методы `is()` / `isNot()` / `inArray()` скопированы в оба enum (`TaskStatus`, `TaskPriority`) один в один. Кандидат на общий trait.
- `due=today` / `due=upcoming` считают «сегодня» по UTC (`now()->startOfDay()` … `endOfDay()`), часовой пояс пользователя API не знает. Вживую: «Созвон с командой» со сроком 24.11 23:00 UTC (= 25.11 02:00 по Москве) попал в «сегодня» 24.11, хотя для пользователя в Москве это завтра. Решить: принимать пояс клиента (`tz`) или считать границы на фронте.
- Поиск не экранирует спецсимволы `LIKE`: `%` и `_` из запроса работают как шаблоны. Вживую `?search=_` вернул все 6 задач. SQL-инъекции нет (значение передаётся параметром), но поиск по этим символам ведёт себя неожиданно.
- **Форма ответа зависит от побочного эффекта политики.** (Документ `2026-08-07-admin-tasks-index.md` прямо утверждает «Публичные task endpoint-ы не отдают эти поля» — для `show` это неправда.) `GET /users/{u}/todo-lists/{l}/tasks/{t}` отдаёт лишние поля `is_list_archived` и `user_id`, а список задач и ответ `PATCH` той же задачи — нет (проверено). Причина: `TaskPolicy::view()` обращается к `$task->todoList` и тем самым подгружает связь, а `TaskResource` добавляет эти поля, если связь загружена (`when(relationLoaded('todoList'))`). `update()` в политике идёт через запрос `todoList()` и связь не грузит. Решить явно: либо грузить `todoList` во всех публичных ответах задачи, либо не показывать эти поля вне админки.
- `TaskPolicy` проверяет «список не архивный» двумя способами: `create` — методом модели `$todoList->isArchived()`, а `update`/`delete` — отдельным SQL-запросом `whereNull('archived_at')` в `ownerHasActiveList()`. Правило «что значит архивный» записано в двух местах, плюс лишний запрос в базу при каждой правке задачи (список уже загружен маршрутом). Можно свести к `$task->todoList->user_id === $user->id && ! $task->todoList->isArchived()`.
- `recent_changes` можно собрать одним SQL-запросом через `UNION ALL` трёх таблиц с `ORDER BY updated_at DESC LIMIT 10` (проверено в psql). Сортировка в PHP — выбор простоты, а не ограничение SQL.
- `DashboardController`: константа `RecentChangesLimit` названа не по PSR-1 (константы класса — `UPPER_SNAKE_CASE`: `RECENT_CHANGES_LIMIT`). Поле `sort_at` в `recentChanges()` дублирует `updated_at` (та же строка) и потом вырезается отдельным `map` — можно сортировать прямо по `updated_at`. При одинаковом `updated_at` порядок в `recent_changes` случайный (та же проблема секундной точности, что у списков).
- `Admin/TaskController::applyTaskFilters()` принимает запрос, разобранный на 8 позиционных параметров (`$search, $status, $priority, $tagId, $due, $sort, $direction`) — легко перепутать порядок. А публичный `TaskController::index` этот метод вообще не использует, а держит свою копию. Шесть FormRequest'ов задач (~700 строк) отличаются друг от друга лишь `authorize()` и источником «чей тег» — кандидат на общий базовый класс или trait.
- `before()` с одинаковым смыслом («админ — всё, заблокированный — только `viewAny`/`view`») скопирован в три политики (`TodoListPolicy`, `TagPolicy`, `TaskPolicy`) и написан двумя стилями: `match` в одной, `in_array` в двух других. Кандидат на общий trait.
- Внешние ключи в `#[Fillable]` не нужны: `user_id` у `TodoList` и `Tag`, `todo_list_id` у `Task`. Записи создаются только через связь (`$user->todoLists()->create(...)`), а она ставит ключ сама через `setAttribute()`, в обход белого списка. Фабрики тоже его обходят (`Model::unguarded`). Лишний пункт в списке — открытая дверь для подмены владельца, если кто-то однажды сделает `TodoList::create($request->all())`.
- Конфликт уникальности ловится вручную по кодам SQLSTATE `['23000', '23505']` (`UserController::store`, `TagController::throwTagNameConflict`). В Laravel есть готовый `Illuminate\Database\UniqueConstraintViolationException` — он не зависит от СУБД (проверено: Postgres бросает именно его). К тому же `23000` в SQLite означает любое нарушение целостности (NOT NULL, FK), не только дубль.
- `(int) ($this->todo_lists_count ?? 0)` в `UserResource` молча отдаёт 0, если счётчики забыли загрузить через `withCount`/`loadCount`. Ошибка разработчика маскируется правдоподобным значением. Обсудить.
- Нормализация имени (squish + lower) повторяется в нескольких местах: `StoreUserRequest`, `StoreTagRequest`, `UpdateTagRequest`, `UserFactory`, `TagFactory` (там только lower), поиск в `Admin/UserController` (`mb_strtolower`). Кандидат на один общий хелпер.
- `storage/schemaspy/schemaspy.sql` нигде не применяется: нет ни make-команды, ни шага CI, ни запуска самого SchemaSpy. В живой БД комментариев нет (`obj_description('users')` пустой). Решить: подключить (make-цель: применить комментарии и сгенерировать HTML-схему образом SchemaSpy) или считать файл просто документацией.
- `tasks.status` и `tasks.priority` — `varchar(20)` без CHECK-ограничения: БД примет `status = 'whatever'` (проверено). Допустимые значения держит только валидация (`Rule::enum`) и enum-cast модели. Цена проверена: если такая строка попадёт в базу, любое чтение задачи моделью падает с `ValueError: "whatever" is not a valid backing value`. Обсудить: добавить CHECK или осознанно оставить.
- БД не гарантирует, что тег и задача в `task_tag` принадлежат одному профилю. Это держит только валидация `tag_ids.*` (`exists ... where user_id`). Через FK такое правило просто не выразить — знать и не обходить валидацию.
- В миграции `0001_01_01_000000_create_users_table` closure в `Schema::create` без `: void`, а в доменной миграции — с ним. Мелочь, но стиль разный.
- `AdminSeeder` хэширует пароль сам (`Hash::make`), хотя у модели `Admin` есть cast `'password' => 'hashed'`, который делает то же и не хэширует повторно уже готовый хэш (проверено). Лишний вызов, безвредно.
- `AdminSeeder` использует `firstOrCreate` по email: повторный запуск ничего не меняет (проверено), но и **не обновит пароль**, если поменять `ADMIN_PASSWORD` в `compose.yml`. Сменить пароль существующего админа можно только вручную — стоит знать.
- **CI фактически не работает:** `origin` — GitHub (`github.com/itdzuj1/ToDoTest`), а пайплайн описан только в `.gitlab-ci.yml`; папки `.github/workflows` нет. Ни lint, ни тесты при push не запускаются. Плюс образ `composer:2` в CI сейчас на PHP 8.5.13, а Docker-образ backend — PHP 8.4.26: CI проверял бы не ту версию. И в CI нет ни Postgres, ни e2e.
- `api.md` про `list_archived` пишет «boolean; `true` — только архивные», то есть подталкивает слать `true`, а сервер такое отвергает (см. баг выше). Про `due=today` — «текущая календарная дата» без уточнения, что по UTC.
- `frontend/components/ui/UiInput.vue:19` — предупреждение eslint `vue/html-self-closing` (`<input/>`). Исправляется `eslint --fix`.
- `tests/Unit` — только заглушка `expect(true)->toBeTrue()`. Чистая логика (методы enum, `Task::isOverdue`) проверяется Feature-тестами с базой. Решить, нужны ли настоящие unit-тесты.
- В `config/database.php` остались неиспользуемые подключения шаблона (`mysql`, `mariadb`, `sqlsrv`, `redis`). Безвредно, но шумно — решить, чистить ли.
- **Баг (подтверждён вживую):** 403 отдаёт `{"code":"http_error"}` вместо `forbidden`. `Handler::render()` вызывает `prepareException()` до render-callback'ов, и `AuthorizationException` приходит в `ApiExceptionRenderer` уже как `AccessDeniedHttpException`, а в `httpError()` нет ветки для 403. Ветки `AuthorizationException`, `ModelNotFoundException` и `TokenMismatchException` в рендерере мёртвые: 404 и 419 получают правильный `code` только через `httpError()`.
- Дыры в `UserApiTest` (проверено мутацией): если убрать `is_string` из `StoreUserRequest::prepareForValidation()`, все 9 тестов остаются зелёными, а живой `POST {"name": 123}` создаёт пользователя «123». В датасете «non-string name» только массив, числа нет — добавить `'numeric name' => [['name' => 123]]`. Ветка `catch` для гонки в `UserController::store` тоже не покрыта.
- Тесты проверяют статусы (`assertForbidden()`), но не `code` в теле. Ни 403, ни 404, ни 419 по `code` не проверяются — поэтому баг выше и не пойман.
- **`ApiExceptionRenderer` теряет заголовки HTTP-исключений.** Он собирает новый `response()->json(...)`, не перенося `$exception->getHeaders()`. Вживую ответ 429 на вход админа пришёл без `Retry-After` и `X-RateLimit-*`, хотя `ThrottleRequestsException` их несёт (`Retry-After: 60`). Фронт не может показать «попробуйте через N секунд». То же касается `Allow` у 405.
- 500 не следует контракту `{code, message}`: рендерер возвращает `null`, и Laravel отдаёт при `APP_DEBUG=true` `{message, exception, file, line, trace}`, а без debug — `{"message":"Server Error"}`.

## Вопросы, к которым вернуться

—

## Заметки по ходу

- Middleware в Laravel 13 (сверено в Docker). Глобальные: `ValidatePathEncoding`, `InvokeDeferredCallbacks`, `TrustProxies`, `HandleCors`, `PreventRequestsDuringMaintenance`, `ValidatePostSize`, `TrimStrings`, `ConvertEmptyStringsToNull`. Группа `web`: `EncryptCookies`, `AddQueuedCookiesToResponse`, `StartSession`, `ShareErrorsFromSession`, `PreventRequestForgery` (CSRF), `SubstituteBindings`. Группа `api`: только `SubstituteBindings`.
- `?search=` и `?search=%20%20` → `null` → фильтра нет, 200. `?search=%20report%20` → `report`, обрезка видна даже в `links` пагинации. `?status=` → 422 `required`.
- Порядок этапов FormRequest (сверено в `ValidatesWhenResolvedTrait::validateResolved`): `prepareForValidation()` → `authorize()` → `rules()` → `after()`. Очистка идёт **до** проверки прав.
- `Str::squish()` без типа параметра: число `123` превращает в строку `"123"`. Поэтому `is_string` в `prepareForValidation()` нужен, чтобы число не «отмылось» в строку и не прошло правило `string`.
- `--filter=UserApiTest` ищет подстроку и захватывает `AdminUserApiTest`. Один файл точно: `php artisan test tests/Feature/UserApiTest.php`.
- Встроенный сервер `php -S` по умолчанию обрабатывает запросы **по одному**, поэтому гонку двух одновременных запросов локально не воспроизвести.
- `->setStatusCode(201)` после создания формально избыточен: `ResourceResponse::calculateStatus()` сам ставит 201 для модели с `wasRecentlyCreated`. Явный вызов просто делает намерение видимым.
- Тип возврата `never` у `TagController::throwTagNameConflict()` говорит PHPStan, что после `catch` выполнение не продолжится. Проверено: с `void` вместо `never` PHPStan пишет `Variable $tag might not be defined` (строка 43).
- Правило `integer` в Laravel принимает и число `6`, и строку `"6"`; `distinct:strict` считает их разными, поэтому `tag_ids: [6, "6"]` проходит валидацию. Безвредно: `tagIds()` приводит к int, а `sync()` схлопывает повтор — тег ставится один раз (проверено).
- Поиск обязан быть в скобках (`where(fn ($q) => ...->orWhereLike(...))`). Без них `OR` выходит за пределы списка: `todo_list_id = 8 AND title ILIKE x OR description ILIKE x`. Вживую поиск «секрет» без скобок нашёл задачу **Петра** из чужого списка.
- Сортировка задач: без `CASE` приоритет сортируется по алфавиту (`desc` → medium, low, high), а Postgres при `DESC` ставит `NULL` первыми (задачи без срока впереди). `CASE` в `orderByRaw` даёт доменный порядок high → medium → low и «без срока — всегда в конце» (проверено).
- `paginate()` делает два запроса: `count(*)` для `meta.total` и выборку с `limit/offset`. `withQueryString()` сохраняет фильтры и сортировку в ссылках `links.prev/next`. Страница за пределами (`page=99`) → 200 с пустым `data`.
- `with('tags')` убирает проблему N+1: 6 задач с тегами — 3 запроса вместо 8 (без него на каждую задачу свой запрос за тегами).
- `now()` возвращает **изменяемую** дату: `$now->startOfDay()` без `copy()` сдвигает сам `$now` на 00:00. Поэтому в фильтре `due` везде `$now->copy()->...`.
- `attach()` добавляет строки в `task_tag` (повторный `attach` того же тега → `UniqueConstraintViolationException` из-за составного PK), `sync()` делает набор ровно таким, как передано, и безопасен при повторе.
- Политики находятся автоматически по имени: `App\Models\User` → `App\Policies\UserPolicy`. Регистрировать их не нужно.
- `before()` политики вызывается, только если в ней **есть метод** под запрошенное право. Для `delete` или `fly` у `UserPolicy` метода нет, поэтому отказано даже админу, хотя его `before()` возвращает `true` (проверено в tinker).
- Последовательности (`*_id_seq`) не откатываются вместе с транзакцией: после демо каскадов первая настоящая задача получит id=4, а не 1.
- В локальной БД для экспериментов: профиль id=1 «Разбор Демо» со списком id=1 «Шаг 0.2»; профиль id=2 «Заблокированный Демо» (заблокирован через админку); id=3 «Анна Смирнова» и id=4 «Пётр Иванов» — созданы через `POST /api/users` в шаге 2.3. Списки (шаг 3.3): у Анны id=8 «Работа и учёба» (активный) и id=9 «Личные дела» (**в архиве** — для примеров read-only в блоке 5), у Петра id=10 «Дача». Теги (шаг 4.2): у Анны id=6 «Срочно», id=7 «Работа»; у Петра id=8 «Срочно», id=9 «Магазин». Задачи (шаг 5.2): id=12 «Сделать отчёт» в списке Анны 8, id=13 «Купить хлеб» в списке Петра 10; (шаг 5.3) id=17 «Подготовить презентацию» в списке 8 — in_progress, high, срок 2026-11-30 15:00 UTC, теги «Срочно» и «Работа»; (шаг 5.4) в списке 8: id=21 «Отправить счёт» (low, просрочена, «Срочно»), id=22 «Созвон с командой» (срок 24.11 23:00 UTC, «Работа»), id=23 «Купить подарок» (done), id=24 «Прочитать статью» (low, без срока, описание про Laravel).
- 2026-11-17: `composer test` — 180 тестов / 699 проверок, `format:test` — 84 файла без замечаний, `analyse` — 55 файлов без ошибок. Коммит `e3e885c` проверен.
- Зачем в `phpunit.xml` пары `<env force>` + `<server>`: Docker кладёт переменные в `$_SERVER`, `$_ENV` и `getenv()`. PHPUnit `<env force>` переписывает только `$_ENV` и `putenv`, а `<server>` — `$_SERVER`. Laravel (phpdotenv) читает сначала `$_SERVER`. Без `<server>` тесты пошли бы в рабочий Postgres, и `RefreshDatabase` стёр бы его.
- Вход админа (проверено curl'ом с банкой cookie): `csrf-cookie` → 204 и две cookie; `login` → номер сессии **меняется** (`session()->regenerate()`, защита от подмены сессии); `DELETE session` без `X-XSRF-TOKEN` → 419, с ним → 204 и снова новый номер сессии (`invalidate()`); после выхода `GET session` → 401.
- Неверный пароль и несуществующий email дают **одинаковый** ответ (422, `email: The provided credentials are invalid.`) — по ответу нельзя узнать, есть ли такой админ.
- bcrypt добавляет случайную соль: один пароль даёт разные хэши, а `Hash::check` берёт соль из сохранённого хэша.
- Лимит входа — 5 попыток в минуту на пару «email + IP» (ключ — sha256). Шестая → 429; другой email с того же IP не заблокирован.
- Поиск пользователей в админке ищет и по `name`, и по `normalized_name` с `mb_strtolower`. В Postgres второе условие лишнее (`whereLike` → `ILIKE` и так без учёта регистра), но оно спасает тесты на SQLite: там `LIKE` без учёта регистра работает только для латиницы (`'Анна' LIKE '%АННА%'` → 0, проверено). Общий вывод: тесты на SQLite не поймают ошибки регистра в кириллическом поиске.
- Админ-эндпоинты проверяют права дважды: middleware `auth:admin` на группе и `authorize(): $this->user('admin') instanceof Admin` в FormRequest'ах. Второе — страховка на случай, если маршрут случайно окажется вне группы.
- В тестах CSRF не проверяется вообще — `PreventRequestForgery::runningUnitTests()` пропускает проверку. Защиту CSRF видно только вживую (проверено в блоке 6).
- Сессии и кэш живут в Postgres: `SESSION_DRIVER=database` → таблица `sessions`, `CACHE_STORE=database` → таблица `cache`. Там же счётчики лимита входа: ключ `todotest-cache-<md5>`, значение — число попыток. Успешный вход тоже считается попыткой.
- `sessions.user_id` хранит **id админа** (default guard — `admin`), хотя таблица `users` — это демо-профили. Имя колонки из шаблона Laravel, внешнего ключа нет.
- Каждый запрос к группе `web` без cookie создаёт новую строку в `sessions`. Старые чистит «лотерея» `[2, 100]` — сборка мусора с вероятностью 2% на запрос.
- CORS проверяет **браузер**, а не сервер. Для чужого Origin сервер всё равно отдаёт данные (curl их получает), но заголовок `Access-Control-Allow-Origin: http://localhost:3000` не совпадёт, и браузер не отдаст ответ скрипту чужого сайта.
- `PreventRequestForgery` (Laravel 13) пропускает запрос без токена, если `Sec-Fetch-Site: same-origin`. Фронт на `:3000` → API на `:8000` — это `same-site`, а не `same-origin`, поэтому фронту нужен `X-XSRF-TOKEN`. Он уже отправляется из `client.ts`.
