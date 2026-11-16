---
paths:
  - "frontend/**/*.{vue,ts,scss}"
---

# Nuxt frontend

Дополняет раздел «Архитектура frontend» в `CLAUDE.md`. Подробный референс — `docs/frontend-implementation-plan.md`, контракты компонентов — `docs/components.md`, состояния — `docs/states.md`.

## Источник истины

- Технические стандарты ToDoTest важнее кода, скопированного из Navision. Navision — референс структуры компонентов, именования и UI-паттернов, не drop-in код.
- Сохраняй масштаб маленького проекта: без абстракций без реальной переиспользуемой ответственности. Не трогай несвязанные файлы.

## Структура

```text
frontend/
├── pages/            только композиция экрана и оркестрация роута
├── layouts/          default.vue (public), admin.vue
├── middleware/       profile.ts, admin-auth.ts
├── components/
│   ├── ui/           Ui* — примитивы
│   ├── elements/     El* — composed-элементы
│   ├── patterns/     Pt* (+ patterns/filters/)
│   └── templates/    Tm* (+ templates/layouts/, templates/sheets/)
├── composables/      типизированное поведение с реальным общим владельцем
├── services/api/     единственное место с путями эндпоинтов
├── types/
├── utils/            чистые функции без Vue и API-состояния
├── plugins/
└── assets/styles/{tokens,themes,_mixins.scss,_reset.scss,main.scss}
```

## Запрещено

- Tailwind и любые utility-CSS фреймворки;
- JavaScript-only компоненты и хелперы (всё на TS);
- Vue mixins и глобальный event bus;
- `any`, non-null assertions и проглоченные ошибки без задокументированной причины;
- пути эндпоинтов вне `services/api`;
- Bearer-авторизация и `useAuth` (в том числе из Navision).

## SFC

Порядок блоков: `template` → `script setup lang="ts"` → `style scoped lang="scss"`.

Порядок в script: imports → props/emits → composables/stores → state → computed → watchers/lifecycle → handlers.

Коммуникация — props, emits, slots. Локальное UI-состояние остаётся в компоненте. Pinia — только для состояния, которое делят независимые фичи с понятным владельцем.

## Именование

| Префикс | Ответственность | Примеры |
|---|---|---|
| `Ui` | визуальный примитив, ноль знаний о домене и API | `UiButton`, `UiInput`, `UiSheet` |
| `El` | composed-элемент форм, a11y-обвязка | `ElForm`, `ElSelect`, `ElInputCalendar` |
| `Pt` | переиспользуемый паттерн; знает доменные типы, не эндпоинты | `PtTable`, `PtFilter`, `PtTaskCard` |
| `Tm` | крупный блок, layout или sheet; владеет своим состоянием | `TmSidebar`, `TmAdminTaskSheet` |
| `use` | типизированный composable | `useTableData`, `useAdminSession` |
| `format` | чистый форматтер | `formatDate`, `formatTaskPriority` |

Фичевые компоненты тоже получают префикс слоя (`PtTaskFilters`, `TmAdminDashboard`), а не голое имя вроде `TodoCard`.

## Typed API layer

Неймспейсы: `api.profiles`, `api.todoLists`, `api.tasks`, `api.tags`, `api.admin.auth`, `api.admin.dashboard`, `api.admin.users`, `api.admin.tasks`.

- 401, 403, 419, 422 и 204 обрабатываются централизованно (`services/api/client.ts`, `errors.ts`).
- Один формат ответа; не разворачивай разные эндпоинты по-разному.
- `{ errors: { field: string[] } }` маппится в поля формы через общую утилиту.
- Пагинация — один адаптер (`normalizeApiPagination`) и одна структура data/meta.
- Успешная мутация заменяет локальные данные возвращённым Resource.
- Меняется контракт backend — обнови типы в `types/` и тесты.

## Публичная и админская зоны

`useDemoProfile()` — выбранный демо-профиль; `useAdminSession()` — cookie-сессия админа и CSRF. Это разные ответственности.

Admin middleware загружает текущую сессию, редиректит неаутентифицированного на `/admin/login`, по возможности сохраняет целевой роут и явно обрабатывает истёкшую сессию. Авторизацию enforce'ит backend; скрытая кнопка — только UX.

## Перенос компонентов из Navision

Для каждого перенесённого компонента:

1. props, emits и slots — на strict TypeScript;
2. убрать Tailwind-классы, визуальные значения — в SCSS-токены;
3. заменить Navision API и доменные данные на контракты ToDoTest;
4. проверить SSR и client-only поведение;
5. ARIA-атрибуты и видимый keyboard focus;
6. для оверлеев — Escape, focus trap, возврат фокуса, блокировка scroll body;
7. focused unit-тест;
8. lint, format check, typecheck, тесты.

Порядок переноса: `UiButton`/`UiInput`/`UiBadge`/`UiSpinner`/`UiTable` → `UiCard`/`UiEmpty`/`UiPagination` → `UiSheet`/`UiModal`/`UiPopover` → `ElInput`/`ElSelect`/`ElForm`/`ElInputWrapper` → `PtTable`/`PtFilter`/`PtTabs`/`PtCardInfo` → `TmSidebar`/`TmHeader`/`TmMenu` → todo- и admin-шаблоны.

Не переносить: Bearer-auth и `useAuth`, `useLibrary` и status maps Navision, бизнес-sheets Navision, Tailwind-конфиг, JS-only хелперы, silent catch и непоследовательное разворачивание ответов.

## Вертикальные слайсы

1. UX map, навигация, wireframes, матрица состояний.
2. Frontend foundation: layouts, middleware, API client, типы, базовые UI-примитивы.
3. Выбор и восстановление демо-профиля.
4. Todo lists: создание, переименование, архив, восстановление, табы active/archived.
5. Задачи и теги: создание, редактирование, фильтры, сортировка, дедлайны, read-only.
6. Админка: login, dashboard, users, lists, управление задачами.
7. Responsive, a11y, тесты, финальный production build.

После каждого слайса: остановись, перечисли изменённые файлы и решения, приведи проверки с точными результатами, назови допущения по API — и дождись явного подтверждения перед следующим слайсом.

## Definition of done для слайса

- все вызовы API — через typed API layer;
- компоненты разложены по слоям `Ui`/`El`/`Pt`/`Tm`;
- стили на SCSS-токенах;
- есть loading, empty, error и disabled состояния;
- 422 привязаны к полям, 403 даёт видимый read-only, 204 обработан;
- мутации обновляют локальное состояние из возвращённого Resource;
- есть релевантные unit- и e2e-тесты;
- `npm run lint`, `format:check`, `typecheck`, `test:run`, `test:e2e`, `build` проходят (`make check` + e2e).
