---
paths:
  - "backend/**/*.php"
  - "backend/storage/schemaspy/schemaspy.sql"
  - "backend/docs/**/*.md"
---

# Laravel backend

Дополняет раздел «Архитектура backend» и «Конвенции кода» в `CLAUDE.md`.

## Слои

- Route → FormRequest → Controller → (Action/Service) → Model → Resource.
- Контроллер тонкий: authorize, оркестрация, явный ответ.
- FormRequest — авторизация, нормализация и валидация; сохраняются только validated-данные.
- Action/Service — только для целого use case, транзакции или нетривиального переиспользуемого правила.
- Авторизация — через Policies, без глобальных permission-хелперов.
- Модели не читают состояние HTTP-запроса (`request()` внутри модели запрещён).
- Только bindings Query Builder; никакой интерполяции ввода в SQL.
- Observers тонкие; тяжёлые побочные эффекты — events/listeners/jobs.
- Миграции анонимные, с типизированными `up(): void` / `down(): void`.
- Доменные варианты в фабриках — через factory states.

## Замыкания — arrow functions

```php
// ❌
->map(function ($item) {
    return $item->id;
})

// ✅
->map(fn ($item) => $item->id)
->when($condition, fn ($query) => $query->where('status', TaskStatus::Done))
```

Обычный `function` — только если тело не помещается в одно выражение (несколько statements, `try/catch`, ранний `return` с побочными эффектами).

## `whereRelation` вместо `whereHas`

```php
// ❌
$query->whereHas('todoList', fn ($query) => $query->where('user_id', $userId));

// ✅
$query->whereRelation('todoList', 'user_id', $userId);

// ✅ сложное условие
$query->whereRelation('todoList', fn ($query) => $query
    ->where('user_id', $userId)
    ->whereNull('archived_at'));
```

`whereHas` — только если `whereRelation` не умеет нужную логику; closure и тогда стрелочная.

## Backed enum

Query builder сам приводит enum к scalar — передавай case без `->value`:

```php
// ❌
$query->whereIn('status', [TaskStatus::Todo->value, TaskStatus::InProgress->value]);

// ✅
$query->whereIn('status', [TaskStatus::Todo, TaskStatus::InProgress]);
```

Сравнение — методами enum (`is()` / `isNot()` / `inArray()` определены в `app/Enums/*`), не `===` и не `in_array`:

```php
// ❌
if ($task->status === TaskStatus::Done) { ... }

// ✅
if ($task->status->is(TaskStatus::Done)) { ... }
if ($task->status->inArray([TaskStatus::Todo, TaskStatus::InProgress])) { ... }
```

Типы параметров — enum, не `int`/`string`. HTTP → enum один раз на границе, в FormRequest-аксессоре.

`->value` допустим только там, где нужен scalar: ключ перевода, внешний API, `sprintf`, `tryFrom()` при чтении сырого значения без cast.

## Изменение API-контракта

Любое изменение запроса/ответа (Resource, FormRequest, routes, поля и их типы, новые обязательные поля, новые/удалённые эндпоинты) → файл `backend/docs/api-changes/{YYYY-MM-DD}-{feature-slug}.md`. Чистый internal refactor без изменения JSON — не нужен.

```markdown
# {Feature} — изменения API

## Затронутые эндпоинты
- `GET /api/...` — ...

## Изменения в ответе
| Поле | Было | Стало | Примечание |
|------|------|-------|------------|
| `data.old_field` | string | — | **удалено** |
| `data.new_field` | — | boolean | **добавлено** |

## Изменения в запросе
...

## Breaking changes
- ...

## Migration notes для фронта
- ...
```

Плюс обновить `backend/docs/api.md` и типы во `frontend/types/`.

## SchemaSpy

Любая миграция, меняющая таблицы/колонки, синхронизируется с `backend/storage/schemaspy/schemaspy.sql`:

- новая колонка — `COMMENT ON COLUMN {table}.{column} IS 'Описание на русском';`
- удалённая — убрать её comment; переименованная — удалить старый, добавить новый;
- новая таблица — `COMMENT ON TABLE` и `COMMENT ON COLUMN` для каждой колонки;
- deprecated-колонка — `IS 'Deprecated'`.

Стиль описаний — как у существующих записей в файле.

## Чеклист задачи с миграцией / контрактом

- [ ] миграция создана
- [ ] `schemaspy.sql` обновлён
- [ ] если менялся API — файл в `backend/docs/api-changes/`, `backend/docs/api.md`, `frontend/types/`
- [ ] `composer format:test`, `composer analyse`, `composer test` проходят
