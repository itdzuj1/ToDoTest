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

## Ресурсы домена

Публичные endpoint-ы пользователей, списков, задач и тегов будут добавляться вертикальными срезами.
Зафиксированные Resource-контракты используют следующие поля:

- `UserResource`: `id`, `name`, вычисляемый из `blocked_at` флаг `is_blocked`, `blocked_at`,
  `todo_lists`, timestamps.
- `TodoListResource`: `id`, `user_id`, `title`, вычисляемый из `archived_at` флаг
  `is_archived`, `archived_at`, `tasks`, timestamps.
- `TaskResource`: `id`, `todo_list_id`, `title`, `description`, `status`, `priority`, `due_at`,
  `completed_at`, `is_overdue`, `tags`, timestamps.
- `TagResource`: `id`, `user_id`, `name`, timestamps.
