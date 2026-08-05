# Задачи и теги — изменения API

## Затронутые эндпоинты

Добавлены endpoint-ы тегов:

- `GET /api/users/{user}/tags`;
- `POST /api/users/{user}/tags`;
- `GET /api/users/{user}/tags/{tag}`;
- `PATCH /api/users/{user}/tags/{tag}`;
- `DELETE /api/users/{user}/tags/{tag}`.

Добавлены endpoint-ы задач:

- `GET /api/users/{user}/todo-lists/{todoList}/tasks`;
- `POST /api/users/{user}/todo-lists/{todoList}/tasks`;
- `GET /api/users/{user}/todo-lists/{todoList}/tasks/{task}`;
- `PATCH /api/users/{user}/todo-lists/{todoList}/tasks/{task}`;
- `DELETE /api/users/{user}/todo-lists/{todoList}/tasks/{task}`.

## Изменения в ответе

- Задачи возвращаются через `TaskResource` с загруженным массивом `tags`.
- `GET .../tasks` возвращает пагинированный ответ с `data`, `links` и `meta`.
- `TaskResource.is_overdue` вычисляется для незавершённой задачи со сроком в прошлом.
- Переход в `done` устанавливает `completed_at`, повторный `done` сохраняет его, выход из `done`
  очищает значение.
- Создание задачи/тега возвращает HTTP 201, удаление — HTTP 204 без тела.
- Заблокированный профиль может читать свои задачи и теги, но не может изменять их.
- Задачи архивного списка доступны для чтения, но не для изменения.

## Изменения в запросе

Тег принимает поле `name`: обязательная строка до 50 символов, нормализованная через trim/squish.
Имя уникально внутри профиля без учёта регистра и повторяющихся пробелов.

Задача принимает:

- `title` — обязательная при создании строка до 120 символов;
- `description` — nullable string до 5000 символов;
- `status` — `todo | in_progress | done`;
- `priority` — `low | medium | high`;
- `due_at` — nullable ISO 8601 с часовым поясом;
- `tag_ids` — nullable список уникальных integer ID тегов выбранного профиля.

В `PATCH` поле `tag_ids` полностью заменяет связи. `[]` или `null` очищает теги; отсутствие поля
не меняет связи. Запись задачи и синхронизация тегов выполняются транзакционно.

`GET .../tasks` поддерживает:

- `search`;
- `status`;
- `priority`;
- `tag_id`;
- `due=overdue|today|upcoming|none`;
- `sort=created_at|due_at|priority`;
- `direction=asc|desc`;
- `per_page=1..100`;
- `page`.

## Breaking changes

- Нет: добавлены новые публичные endpoint-ы.

## Migration notes для фронта

- Загружать задачи отдельным пагинированным запросом `GET .../tasks`; `TodoListResource` не включает
  задачи без явной загрузки связи.
- Для полной замены тегов отправлять `tag_ids`; не отправлять поле, если связи нужно сохранить.
- Ошибки чужих и неизвестных `tag_ids` отображать из `errors.tag_ids.*`.
- При read-only состоянии заблокированного профиля или архивного списка учитывать HTTP 403
  на `POST`, `PATCH` и `DELETE`.
- Для просроченного представления использовать `due=overdue` либо поле `is_overdue` ресурса.
