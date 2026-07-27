# Backend domain foundation — изменения API

## Затронутые эндпоинты

- `GET /api` — добавлена базовая информация об API.
- `GET /api/admin/csrf-cookie` — добавлена инициализация CSRF cookie.
- `POST /api/admin/login` — добавлен вход администратора.
- `GET /api/admin/session` — добавлено получение текущего администратора.
- `DELETE /api/admin/session` — добавлен выход администратора.

## Изменения в ответе

- Добавлен `AdminResource`.
- Зафиксированы будущие контракты `UserResource`, `TodoListResource`, `TaskResource` и `TagResource`.
- Ошибки валидации используют `{ "errors": { "field": ["message"] } }`.
- Остальные ожидаемые ошибки используют `{ "code": "...", "message": "..." }`.

## Изменения в запросе

- Для admin endpoint-ов требуется cookie-session и CSRF-заголовок.
- `POST /api/admin/login` принимает `email` и `password`.

## Breaking changes

- Отсутствуют: публичные доменные endpoint-ы ранее не существовали.

## Migration notes для фронта

- API-клиент должен отправлять запросы с `credentials: 'include'`.
- Перед входом нужно вызвать `/api/admin/csrf-cookie`.
- Значение cookie `XSRF-TOKEN` нужно передавать в заголовке `X-XSRF-TOKEN`.
- HTTP 401 означает отсутствие admin-сессии, HTTP 419 — устаревший CSRF-токен.
