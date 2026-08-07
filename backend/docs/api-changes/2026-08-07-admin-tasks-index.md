# Admin tasks index — изменения API

## Затронутые эндпоинты

- `GET /api/admin/tasks` — **добавлено**

Существующие:

- `GET /api/admin/todo-lists/{todoList}/tasks`
- `PATCH /api/admin/tasks/{task}`
- `DELETE /api/admin/tasks/{task}`

## Изменения в ответе

| Поле / ответ | Было | Стало | Примечание |
|---|---|---|---|
| `GET /api/admin/tasks` | — | paginated `TaskResource` | с `tags`, owner и archive meta |
| `TaskResource.is_list_archived` | — | `boolean` | только если загружен `todoList` |
| `TaskResource.user_id` | — | `number` | только если загружен `todoList` |
| `TaskResource.user_name` | — | `string` | только если загружен `todoList.user` |

Публичные task endpoint-ы не отдают эти поля (relation не загружается).

## Изменения в запросе

`GET /api/admin/tasks` принимает:

- `search` — опционально, max 120;
- `user_id` — опционально, должен существовать;
- `status` — `todo|in_progress|done`;
- `priority` — `low|medium|high`;
- `tag_id` — опционально; если передан `user_id`, тег должен принадлежать этому user;
- `due` — `overdue|today|upcoming|none`;
- `list_archived` — boolean; `true` — только задачи архивных списков, `false` — только активных, omit — все;
- `sort` — `created_at|due_at|priority`, default `created_at`;
- `direction` — `asc|desc`, default `desc`;
- `per_page` — 1..100, default 20;
- `page` — ≥ 1.

## Breaking changes

Нет.

## Migration notes для фронта

- `pages/admin/tasks.vue` использует `GET /api/admin/tasks`.
- Колонка пользователя: `user_name` + `user_id`.
- Маркер архива: `is_list_archived`.
- Чип «Архивные списки» → `list_archived=1`.
