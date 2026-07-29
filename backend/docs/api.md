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

## Ресурсы домена

Публичные endpoint-ы пользователей, задач и тегов будут добавляться следующими вертикальными срезами.
Зафиксированные Resource-контракты используют следующие поля:

- `UserResource`: `id`, `name`, вычисляемый из `blocked_at` флаг `is_blocked`, `blocked_at`,
  `todo_lists`, timestamps.
- `TodoListResource`: `id`, `user_id`, `title`, вычисляемый из `archived_at` флаг
  `is_archived`, `archived_at`, `tasks`, timestamps.
- `TaskResource`: `id`, `todo_list_id`, `title`, `description`, `status`, `priority`, `due_at`,
  `completed_at`, `is_overdue`, `tags`, timestamps.
- `TagResource`: `id`, `user_id`, `name`, timestamps.
