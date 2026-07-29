# Todo list lifecycle — изменения API

## Затронутые эндпоинты

- `GET /api/users/{user}/todo-lists` — добавлен список с фильтром `active|archived|all`.
- `POST /api/users/{user}/todo-lists` — добавлено создание списка.
- `GET /api/users/{user}/todo-lists/{todoList}` — добавлен просмотр списка.
- `PATCH /api/users/{user}/todo-lists/{todoList}` — добавлено переименование.
- `PATCH /api/users/{user}/todo-lists/{todoList}/archive` — добавлено архивирование.
- `PATCH /api/users/{user}/todo-lists/{todoList}/restore` — добавлено восстановление.

## Изменения в ответе

- Collection endpoint возвращает `{ "data": TodoListResource[] }`.
- Остальные успешные endpoint-ы возвращают `{ "data": TodoListResource }`.
- Создание возвращает HTTP 201; остальные успешные операции — HTTP 200.
- `is_archived` вычисляется из `archived_at`.

## Изменения в запросе

- `title` обязателен для создания и переименования, нормализуется и ограничен 120 символами.
- Одинаковые названия списков разрешены.
- `filter` по умолчанию равен `active`; неизвестное значение возвращает HTTP 422.

## Правила доступа

- `{todoList}` всегда проверяется в рамках `{user}`; чужой список возвращает HTTP 404.
- Заблокированный профиль сохраняет чтение, но изменения возвращают HTTP 403.
- Архивный список нельзя переименовать до восстановления.
- Повторные archive/restore запросы идемпотентны.

## Breaking changes

- Отсутствуют: endpoint-ы todo-списков ранее не существовали.

## Migration notes для фронта

- Хранить выбранный `user_id` и подставлять его в nested URL.
- Для вкладок активных и архивных списков использовать query-параметр `filter`.
- Обрабатывать HTTP 403 как read-only состояние заблокированного или архивного профиля/списка.
- После мутации заменять локальную модель данными из возвращённого `TodoListResource`.
