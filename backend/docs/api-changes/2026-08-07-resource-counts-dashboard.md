# Resource counts and dashboard — изменения API

## Затронутые эндпоинты

- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{user}`
- `GET /api/users/{user}/todo-lists`
- `POST /api/users/{user}/todo-lists`
- `GET /api/users/{user}/todo-lists/{todoList}`
- `PATCH /api/users/{user}/todo-lists/{todoList}`
- `PATCH /api/users/{user}/todo-lists/{todoList}/archive`
- `PATCH /api/users/{user}/todo-lists/{todoList}/restore`
- `GET /api/admin/dashboard`
- `GET /api/admin/users`
- `GET /api/admin/users/{user}`
- `PATCH /api/admin/users/{user}/block`
- `PATCH /api/admin/users/{user}/unblock`
- `PATCH /api/admin/todo-lists/{todoList}/archive`
- `PATCH /api/admin/todo-lists/{todoList}/restore`

## Изменения в ответе

| Поле | Было | Стало | Примечание |
|---|---|---|---|
| `UserResource.todo_lists_count` | — | `number` | **добавлено**, через `withCount` |
| `UserResource.tasks_count` | — | `number` | **добавлено**, через `hasManyThrough` |
| `TodoListResource.tasks_count` | — | `number` | **добавлено** |
| `TodoListResource.completed_tasks_count` | — | `number` | **добавлено**, `status = done` |
| `GET /api/admin/dashboard` → `blocked_users_count` | — | `number` | **добавлено** |
| `GET /api/admin/dashboard` → `recent_changes` | — | `array` | **добавлено**, до 10 элементов |

### `recent_changes` item

```json
{
  "type": "user|todo_list|task",
  "id": 1,
  "label": "Readable title or name",
  "updated_at": "2026-08-07T12:00:00.000000Z"
}
```

Сортировка: `updated_at` desc по объединённому набору последних users/lists/tasks.

## Изменения в запросе

Нет.

## Breaking changes

Нет. Добавлены только поля; существующие клиенты их игнорируют.

## Migration notes для фронта

- Карточки профилей: `todo_lists_count`, `tasks_count`.
- Карточки списков: `tasks_count`, `completed_tasks_count`.
- Admin users: счётчики без email (у demo-users email нет).
- Dashboard: использовать `blocked_users_count` и `recent_changes` вместо клиентских заглушек.
