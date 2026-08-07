# API-контракт ToDoTest

Базовый URL локального backend: `http://localhost:8000/api`.

## Общие правила

- URL используют kebab-case.
- Поля JSON используют snake_case.
- Создание выполняется через `POST`, изменение — через `PATCH`, удаление — через `DELETE`.
- Успешное удаление возвращает HTTP 204 без тела.
- Даты передаются в ISO 8601 с часовым поясом.
- Клиент отправляет cookie с `credentials: 'include'`.

## Ошибки

Ошибка валидации возвращает HTTP 422:

```json
{
    "errors": {
        "field": ["Validation message."]
    }
}
```

Остальные ожидаемые ошибки возвращают стабильный код и безопасное сообщение:

```json
{
    "code": "unauthenticated",
    "message": "Authentication is required."
}
```

Поддерживаемые коды: `unauthenticated`, `forbidden`, `not_found`, `method_not_allowed`,
`csrf_token_mismatch`, `too_many_requests`, `http_error`.

## Сессия администратора

### `GET /api/admin/csrf-cookie`

Создаёт session/XSRF cookies. Перед изменяющими запросами клиент должен URL-декодировать cookie
`XSRF-TOKEN` и отправить значение в заголовке `X-XSRF-TOKEN`.

Успешный ответ: HTTP 204.

### `POST /api/admin/login`

Запрос:

```json
{
    "email": "admin@example.test",
    "password": "secret"
}
```

Успешный ответ:

```json
{
    "data": {
        "id": 1,
        "name": "Local Admin",
        "email": "admin@example.test",
        "created_at": "2026-07-26T12:00:00.000000Z",
        "updated_at": "2026-07-26T12:00:00.000000Z"
    }
}
```

Неверные учётные данные возвращают HTTP 422 с ошибкой поля `email`. Endpoint ограничен пятью запросами в минуту.

### `GET /api/admin/session`

Возвращает текущего администратора в формате `AdminResource`. Без сессии возвращает HTTP 401.

### `DELETE /api/admin/session`

Завершает сессию, инвалидирует session id, обновляет CSRF-токен и возвращает HTTP 204.

## Административное управление

Все endpoint-ы ниже требуют активную admin-сессию. Без сессии возвращается HTTP 401.
Администратор может изменять задачи архивных списков и профилей с `is_blocked: true`.
Полный CRUD пользователей и списков в первую версию не входит.

### `GET /api/admin/dashboard`

Возвращает агрегаты:

```json
{
    "data": {
        "users_count": 12,
        "blocked_users_count": 2,
        "active_lists_count": 20,
        "archived_lists_count": 3,
        "tasks_count": 140,
        "overdue_tasks_count": 7,
        "recent_changes": [
            {
                "type": "task",
                "id": 42,
                "label": "Подготовить отчёт",
                "updated_at": "2026-08-07T12:00:00.000000Z"
            }
        ]
    }
}
```

`overdue_tasks_count` считает незавершённые задачи со сроком в прошлом.
`blocked_users_count` — число профилей с `blocked_at != null`.
`recent_changes` — до 10 последних изменений (`user` / `todo_list` / `task`) по `updated_at`.

### `GET /api/admin/users`

Пагинированная коллекция `UserResource` без вложенных списков. Query-параметры:

- `search` — опциональный поиск без учёта регистра по `name` / `normalized_name`;
- `filter=active|blocked|all`, по умолчанию `all`;
- `per_page` — от 1 до 100, по умолчанию 20;
- `page` — номер страницы.

Сортировка: `normalized_name`, затем `id`.

### `GET /api/admin/users/{user}`

Возвращает `UserResource` с загруженными `todo_lists` без вложенных задач. Списки
упорядочены по `id`. Неизвестный ID возвращает HTTP 404.

### `PATCH /api/admin/users/{user}/block`

Блокирует профиль идемпотентно и возвращает `UserResource`.

### `PATCH /api/admin/users/{user}/unblock`

Разблокирует профиль идемпотентно и возвращает `UserResource`.

### `PATCH /api/admin/todo-lists/{todoList}/archive`

Архивирует список идемпотентно и возвращает `TodoListResource`.

### `PATCH /api/admin/todo-lists/{todoList}/restore`

Восстанавливает список идемпотентно и возвращает `TodoListResource`.

### `GET /api/admin/todo-lists/{todoList}/tasks`

Пагинированная коллекция задач списка с тегами. Query-параметры и семантика совпадают с
публичным `GET /api/users/{user}/todo-lists/{todoList}/tasks`: `search`, `status`,
`priority`, `tag_id`, `due`, `sort`, `direction`, `per_page`, `page`.
`tag_id` должен принадлежать владельцу списка.

### `GET /api/admin/tasks`

Глобальная пагинированная коллекция задач с тегами и метаданными владельца/списка.
Дополнительно к общим фильтрам задач принимает:

- `user_id` — опциональный фильтр по владельцу списка;
- `list_archived` — boolean; `true` — только архивные списки, `false` — только активные.

В элементах ответа дополнительно присутствуют `user_id`, `user_name`, `is_list_archived`.

### `PATCH /api/admin/tasks/{task}`

Частично обновляет задачу. Поля как у публичного update: `title`, `description`, `status`,
`priority`, `due_at`, `tag_ids`. `status` согласует `completed_at` через доменный метод.
`tag_ids` полностью заменяет набор тегов; все ID должны принадлежать владельцу списка задачи.
Работает для архивных списков и заблокированных профилей. Поля вне контракта игнорируются.

### `DELETE /api/admin/tasks/{task}`

Безвозвратно удаляет задачу и возвращает HTTP 204. Подтверждение удаления выполняется на
клиенте.

## Демо-профили

Профили публичны и не являются механизмом авторизации.

### `GET /api/users`

Возвращает коллекцию активных `UserResource`, упорядоченную по нормализованному имени. Заблокированные
профили в коллекцию не входят. Вложенные todo-списки не загружаются.

### `POST /api/users`

Создаёт демо-профиль и возвращает HTTP 201:

```json
{
    "name": "Анна Смирнова"
}
```

`name` обязателен, должен быть строкой длиной до 80 символов и нормализуется через trim/squish.
Имена уникальны без учёта регистра и повторяющихся пробелов. Дубликат возвращает HTTP 422 с ошибкой
поля `name`. Поля вне контракта игнорируются.

### `GET /api/users/{user}`

Возвращает `UserResource` без вложенных todo-списков. Заблокированный профиль доступен по прямому ID
и возвращается с `is_blocked: true`, чтобы клиент мог корректно обработать ранее выбранный профиль.
Неизвестный ID возвращает HTTP 404.

## Todo-списки демо-профиля

Публичные endpoint-ы списков вложены в профиль. Это не авторизация: посетитель передаёт выбранный
`user_id`, а backend проверяет принадлежность списка этому профилю.

Заблокированный профиль может читать свои списки, но создание и любые изменения возвращают HTTP 403.
Список другого профиля в nested URL возвращает HTTP 404.

### `GET /api/users/{user}/todo-lists`

Возвращает коллекцию `TodoListResource`. Query-параметр `filter` принимает:

- `active` — активные списки; значение по умолчанию;
- `archived` — архивные списки;
- `all` — все списки.

Неизвестное значение `filter` возвращает HTTP 422.

### `POST /api/users/{user}/todo-lists`

Создаёт список и возвращает HTTP 201:

```json
{
    "title": "Рабочие задачи"
}
```

`title` обязателен, должен быть строкой длиной до 120 символов и нормализуется через trim/squish.
Одинаковые названия у одного профиля разрешены.

### `GET /api/users/{user}/todo-lists/{todoList}`

Возвращает принадлежащий профилю `TodoListResource`.

### `PATCH /api/users/{user}/todo-lists/{todoList}`

Переименовывает активный список. Формат и правила `title` совпадают с созданием. Архивный список
сначала нужно восстановить; попытка переименования возвращает HTTP 403.

### `PATCH /api/users/{user}/todo-lists/{todoList}/archive`

Архивирует список и возвращает актуальный `TodoListResource`. Повторный запрос идемпотентен:
`archived_at` и `updated_at` не изменяются.

### `PATCH /api/users/{user}/todo-lists/{todoList}/restore`

Восстанавливает список и возвращает актуальный `TodoListResource`. Повторный запрос идемпотентен
и не изменяет `updated_at`.

Пример ответа одного списка:

```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "title": "Рабочие задачи",
        "is_archived": false,
        "archived_at": null,
        "created_at": "2026-07-29T09:00:00.000000Z",
        "updated_at": "2026-07-29T09:00:00.000000Z"
    }
}
```

## Теги демо-профиля

Endpoint-ы тегов вложены в профиль. Заблокированный профиль может читать свои теги, но создание,
переименование и удаление возвращают HTTP 403. Тег другого профиля в nested URL возвращает HTTP 404.

### `GET /api/users/{user}/tags`

Возвращает коллекцию `TagResource`, упорядоченную по нормализованному имени и затем по `id`.

### `POST /api/users/{user}/tags`

Создаёт тег и возвращает HTTP 201:

```json
{
    "name": "Работа"
}
```

`name` обязателен, должен быть строкой длиной до 50 символов и нормализуется через trim/squish.
Имя уникально внутри профиля без учёта регистра и повторяющихся пробелов. Дубликат возвращает
HTTP 422 с ошибкой поля `name`.

### `GET /api/users/{user}/tags/{tag}`

Возвращает принадлежащий профилю `TagResource`.

### `PATCH /api/users/{user}/tags/{tag}`

Переименовывает тег. Формат и правила `name` совпадают с созданием.

### `DELETE /api/users/{user}/tags/{tag}`

Безвозвратно удаляет тег и возвращает HTTP 204. Связи тега с задачами удаляются, задачи сохраняются.

## Задачи todo-списка

Endpoint-ы задач вложены в профиль и todo-список. Чужой список или задача в nested URL возвращают
HTTP 404. Заблокированный профиль и архивный список сохраняют чтение задач, но создание, изменение
и удаление возвращают HTTP 403.

### `GET /api/users/{user}/todo-lists/{todoList}/tasks`

Возвращает пагинированную коллекцию `TaskResource` с загруженными тегами. Ответ содержит стандартные
поля Laravel pagination `data`, `links` и `meta`.

Query-параметры:

- `search` — регистронезависимый поиск по `title` и `description`, максимум 120 символов;
- `status=todo|in_progress|done`;
- `priority=low|medium|high`;
- `tag_id` — ID тега выбранного профиля;
- `due=overdue|today|upcoming|none`;
- `sort=created_at|due_at|priority`;
- `direction=asc|desc`, по умолчанию `desc`;
- `per_page` — от 1 до 100, по умолчанию 20;
- `page` — номер страницы, начиная с 1.

Категории срока:

- `overdue` — незавершённые задачи со сроком раньше текущего момента;
- `today` — задачи со сроком в текущую календарную дату независимо от статуса;
- `upcoming` — задачи со сроком после окончания текущей даты;
- `none` — задачи без срока.

По умолчанию задачи сортируются по `created_at desc`, затем `id desc`. При сортировке по сроку
значения `null` всегда располагаются последними. Приоритет сортируется в доменном порядке
`high → medium → low` для `desc` и в обратном порядке для `asc`. Неизвестные или недоступные
значения query-параметров возвращают HTTP 422.

### `POST /api/users/{user}/todo-lists/{todoList}/tasks`

Создаёт задачу и возвращает HTTP 201:

```json
{
    "title": "Подготовить отчёт",
    "description": "Проверить итоговые цифры",
    "status": "in_progress",
    "priority": "high",
    "due_at": "2026-08-10T15:30:00+03:00",
    "tag_ids": [1, 2]
}
```

Правила полей:

- `title` обязателен, нормализуется через trim/squish, максимум 120 символов;
- `description` необязателен, допускает `null`, максимум 5000 символов;
- `status` необязателен, принимает `todo | in_progress | done`, по умолчанию `todo`;
- `priority` необязателен, принимает `low | medium | high`, по умолчанию `medium`;
- `due_at` необязателен, принимает `null` или ISO 8601 с часовым поясом и сохраняется в UTC;
- `tag_ids` необязателен, принимает `null` или список уникальных integer ID тегов выбранного профиля.

Чужой, неизвестный или повторяющийся тег возвращает HTTP 422. Создание задачи, установка статуса
и синхронизация тегов выполняются одной транзакцией.

### `GET /api/users/{user}/todo-lists/{todoList}/tasks/{task}`

Возвращает принадлежащий списку `TaskResource` с тегами.

### `PATCH /api/users/{user}/todo-lists/{todoList}/tasks/{task}`

Частично изменяет задачу и возвращает актуальный `TaskResource`. Правила полей совпадают с созданием,
но все поля необязательны. Переданный `tag_ids` полностью заменяет набор тегов; пустой массив или
`null` очищает набор, отсутствие поля сохраняет текущие связи.

При переходе в `done` backend устанавливает `completed_at`. Повторная установка `done` сохраняет
исходное время завершения. Переход из `done` очищает `completed_at`. Поля `todo_list_id`,
`completed_at` и другие поля вне контракта игнорируются.

### `DELETE /api/users/{user}/todo-lists/{todoList}/tasks/{task}`

Безвозвратно удаляет задачу и её связи с тегами, возвращает HTTP 204.

Пример ответа задачи:

```json
{
    "data": {
        "id": 1,
        "todo_list_id": 1,
        "title": "Подготовить отчёт",
        "description": "Проверить итоговые цифры",
        "status": "in_progress",
        "priority": "high",
        "due_at": "2026-08-10T12:30:00.000000Z",
        "completed_at": null,
        "is_overdue": false,
        "tags": [
            {
                "id": 1,
                "user_id": 1,
                "name": "Работа",
                "created_at": "2026-08-05T09:00:00.000000Z",
                "updated_at": "2026-08-05T09:00:00.000000Z"
            }
        ],
        "created_at": "2026-08-05T09:00:00.000000Z",
        "updated_at": "2026-08-05T09:00:00.000000Z"
    }
}
```

## Ресурсы домена

Зафиксированные Resource-контракты используют следующие поля:

- `UserResource`: `id`, `name`, вычисляемый из `blocked_at` флаг `is_blocked`, `blocked_at`,
  `todo_lists_count`, `tasks_count`, `todo_lists`, timestamps.
- `TodoListResource`: `id`, `user_id`, `title`, вычисляемый из `archived_at` флаг
  `is_archived`, `archived_at`, `tasks_count`, `completed_tasks_count`, `tasks`, timestamps.
- `TaskResource`: `id`, `todo_list_id`, `title`, `description`, `status`, `priority`, `due_at`,
  `completed_at`, `is_overdue`, `tags`, timestamps; опционально `user_id`, `user_name`,
  `is_list_archived` (когда загружен `todoList` / `todoList.user`).
- `TagResource`: `id`, `user_id`, `name`, timestamps.
