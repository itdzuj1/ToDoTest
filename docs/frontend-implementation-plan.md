# План и правила реализации фронтенда ToDoTest

Документ объединяет текущие правила проекта ToDoTest и компонентный подход проекта Navision.

Главное правило: технические стандарты ToDoTest имеют приоритет, а Navision используется как
reference для структуры компонентов, именования и готовых UI-паттернов.

## 1. Цели фронтенда

Фронтенд должен реализовать:

* публичную часть с выбором и восстановлением демо-профиля;
* управление todo-списками;
* создание и редактирование задач;
* статусы, приоритеты, сроки и теги;
* фильтрацию, поиск, сортировку и pagination задач;
* явные loading, empty, error и read-only состояния;
* отдельную административную зону `/admin`;
* admin login через cookie-сессию;
* dashboard, пользователей, списки и задачи в админке;
* подтверждение безвозвратного удаления задач;
* unit-тесты, e2e-тесты, typecheck, lint и production build.

Используется одно Nuxt-приложение. Публичная часть и админка не разделяются на два frontend-проекта.

## 2. Техническая основа ToDoTest

Обязательные решения:

* Nuxt 3;
* Vue 3;
* TypeScript strict;
* Composition API;
* `<script setup lang="ts">`;
* SCSS;
* CSS custom properties для design tokens;
* typed API layer;
* отдельные layouts и route middleware для `/admin`;
* Vitest для unit-тестов;
* Playwright для главных пользовательских сценариев;
* ESLint и Prettier;
* CI-проверки качества и сборки.

Tailwind, JavaScript-only компоненты, mixins и глобальный event bus в реализацию не добавляются.

## 3. Именование компонентов

Сохраняется паттерн именования Navision.

| Префикс | Назначение | Примеры |
|---|---|---|
| `Ui` | низкоуровневый UI-примитив | `UiButton`, `UiInput`, `UiTable`, `UiSheet` |
| `El` | составной универсальный элемент | `ElForm`, `ElSelect`, `ElInputCalendar` |
| `Pt` | переиспользуемый интерфейсный паттерн | `PtTable`, `PtFilter`, `PtTabs`, `PtCardInfo` |
| `Tm` | крупный шаблон, layout или sheet | `TmSidebar`, `TmHeader`, `TmTodoListSheet` |
| `use` | typed composable | `useTableData`, `useAdminSession` |
| `format` | чистая функция форматирования | `formatDate`, `formatTaskPriority` |

В шаблонах используются kebab-case-теги:

```vue
<pt-table />
<tm-sidebar />
<ui-button />
```

Файлы компонентов называются PascalCase:

```text
PtTable.vue
TmSidebar.vue
UiButton.vue
```

Feature-компоненты также сохраняют префикс слоя. Например:

```text
PtTaskCard.vue
PtTaskFilters.vue
TmTodoListSheet.vue
TmAdminDashboard.vue
TmAdminTaskSheet.vue
```

## 4. Структура директорий

```text
frontend/
├── app.vue
├── pages/
│   ├── index.vue
│   ├── lists/
│   └── admin/
├── layouts/
│   ├── default.vue
│   └── admin.vue
├── middleware/
│   ├── admin-auth.ts
│   └── profile.ts
├── components/
│   ├── ui/
│   ├── elements/
│   ├── patterns/
│   │   └── filters/
│   └── templates/
│       ├── layouts/
│       └── sheets/
├── composables/
├── services/
│   └── api/
├── types/
├── utils/
├── lib/
├── plugins/
├── assets/
│   └── styles/
│       ├── tokens/
│       ├── themes/
│       ├── _mixins.scss
│       ├── _reset.scss
│       └── main.scss
└── tests/
    ├── unit/
    └── e2e/
```

### `components/ui`

Низкоуровневые компоненты без бизнес-логики:

* `UiButton`;
* `UiInput`;
* `UiTextarea`;
* `UiSelect`;
* `UiCheckbox`;
* `UiSwitch`;
* `UiBadge`;
* `UiCard`;
* `UiTable`;
* `UiSheet`;
* `UiModal`;
* `UiPopover`;
* `UiCalendar`;
* `UiSpinner`;
* `UiEmpty`;
* `UiPagination`;
* `UiToast`.

`Ui`-компоненты знают только о своих props, emits, slots и визуальном поведении.

### `components/elements`

Составные универсальные элементы:

* `ElForm`;
* `ElInput`;
* `ElSelect`;
* `ElInputCalendar`;
* `ElInputWrapper`;
* `ElIcon`;
* `ElLink`;
* `ElThemeToggle`.

`El`-слой может объединять несколько `Ui`-компонентов, но не должен обращаться к бизнес-API.

### `components/patterns`

Переиспользуемые интерфейсные паттерны:

* `PtTable`;
* `PtFilter`;
* `PtTabs`;
* `PtCardInfo`;
* `PtUserProfile`;
* `PtTaskCard`;
* `PtTaskFilters`;
* `PtListCard`.

Паттерны могут принимать декларативные конфигурации, typed slots и callbacks, но не должны
знать конкретные endpoint URL.

### `components/templates`

Крупные шаблоны и контейнеры экранов:

* `TmHeader`;
* `TmSidebar`;
* `TmMenu`;
* `TmTodoListSheet`;
* `TmTaskSheet`;
* `TmAdminDashboard`;
* `TmAdminUserSheet`;
* `TmAdminTaskSheet`.

Крупный шаблон может использовать API composables, локальное состояние и domain-specific UI.
При этом API-вызовы должны проходить через typed API layer.

## 5. Компоненты, которые стоит перенести из Navision

Переносить можно как reference и заготовки, с обязательной адаптацией.

### Таблица

Источник: `navision/frontend/components/patterns/PtTable.vue`.

Сохранить:

* конфигурацию колонок;
* `field`, `header`, `sortable`, `align`, `filter`;
* nested values через `value`;
* slots для специальных ячеек;
* loading state;
* empty state;
* pagination;
* сортировку;
* обработку клика по строке.

В ToDoTest компонент должен быть typed. Рекомендуемый контракт:

```ts
export interface TableColumn<Row> {
    field: string
    header: string
    sortable?: boolean
    align?: 'left' | 'center' | 'right'
    value?: (row: Row) => unknown
    format?: (value: unknown, row: Row) => string
}
```

Бизнес-компоненты должны использовать `PtTable`, а `PtTable` должен собираться из `UiTable`.

### Фильтры

Источники:

* `navision/frontend/components/patterns/PtFilter.vue`;
* `navision/frontend/components/patterns/filters/`.

Сохранить типы фильтров:

* input;
* select;
* multi-select;
* date;
* button-group.

Поддержать:

* typed filter config;
* debounce для поиска;
* синхронизацию с query-параметрами;
* очистку фильтров;
* корректное отображение активных фильтров.

Для задач фильтры должны поддерживать `search`, `status`, `priority`, `tag_id`, `due`, `sort`,
`direction`, `page` и `per_page`.

### Tabs

Источник: `navision/frontend/components/patterns/PtTabs.vue`.

Использовать для:

* активных и архивных todo-списков;
* фильтров задач;
* разделов admin dashboard;
* переключения списка пользователей в админке.

### Card info

Источник: `navision/frontend/components/patterns/PtCardInfo.vue`.

Использовать для:

* данных выбранного профиля;
* информации о списке;
* детального просмотра задачи;
* информации о пользователе в админке.

### Sheets и модалки

Источник: `navision/frontend/components/ui/sheet/sheet.vue`.

Сохранить общий контракт:

```vue
<ui-sheet
    v-model="isOpen"
    :title="title"
    @confirm="handleConfirm"
>
    ...
</ui-sheet>
```

Обязательные возможности:

* `v-model`;
* overlay;
* Escape;
* закрытие по клику вне панели;
* header, content и footer slots;
* confirm/cancel actions;
* вложенные sheets;
* z-index tokens;
* responsive width.

Перед использованием в ToDoTest проверить и улучшить:

* ARIA-атрибуты;
* focus trap;
* возврат фокуса на trigger;
* блокировку скролла body;
* keyboard navigation;
* мобильную ширину;
* работу при SSR.

## 6. Typed API layer

Endpoint paths хранятся только в `services/api/`.

Рекомендуемая структура:

```text
services/api/
├── client.ts
├── errors.ts
├── pagination.ts
├── public.ts
├── profiles.ts
├── todoLists.ts
├── tasks.ts
├── tags.ts
└── admin.ts
```

Методы API используют camelCase:

```ts
api.todoLists.list(userId, params)
api.todoLists.create(userId, payload)
api.tasks.list(userId, todoListId, params)
api.admin.users.block(userId)
```

Правила:

* URL используют kebab-case;
* JSON-поля используют snake_case;
* методы TypeScript используют camelCase;
* запросы отправляются с `credentials: 'include'`;
* admin login предваряется запросом CSRF cookie;
* `XSRF-TOKEN` передаётся в `X-XSRF-TOKEN`;
* UI-компоненты не собирают URL;
* response shape не unwrap-ится по-разному в разных местах;
* 401, 403, 419, 422 и 204 обрабатываются централизованно;
* 422 маппится на поля формы через общий error utility;
* внутренние исключения и stack traces не показываются пользователю.

### Разделение сессий

Нужно сохранить два независимых состояния:

```text
useDemoProfile()
useAdminSession()
```

`useDemoProfile` работает с выбранным `user_id` в localStorage или cookie.

`useAdminSession` работает с защищённой HttpOnly cookie-сессией, CSRF и `/admin` middleware.

Bearer-auth и `useAuth` из Navision не переносить.

## 7. Composables

Composables создаются только при наличии реальной переиспользуемой ответственности.

Можно адаптировать:

* `useTableData` для pagination, sorting, query filters и reload;
* `useClickOutside` для dropdown и popover;
* `useToggleAction` для block/unblock, archive/restore;
* `useTheme` для переключения темы;
* `normalizeApiPagination` как typed pagination adapter.

Новые composables:

* `useDemoProfile`;
* `useAdminSession`;
* `useTodoLists`;
* `useTasks`;
* `useTags`;
* `useFormErrors`;
* `useTaskFilters`.

Не создавать composable для одной локальной операции. Pinia использовать только для состояния,
которое действительно разделяется несколькими независимыми feature-компонентами.

## 8. Состояния интерфейса

Каждый экран и запрос должен явно описывать:

* initial;
* loading;
* success;
* empty;
* error;
* read-only;
* disabled;
* submitting;
* deleting;
* archived;
* blocked.

Особые состояния проекта:

* заблокированный профиль доступен для чтения;
* заблокированный профиль не может менять данные;
* архивный список доступен для чтения;
* задачи архивного списка нельзя изменять;
* администратор может изменять задачи независимо от blocked или archived состояния;
* удаление задачи требует отдельного подтверждения.

## 9. SCSS и design tokens

Текущую систему ToDoTest сохраняем. Navision CSS и Tailwind-конфигурацию не переносим.

```text
assets/styles/
├── tokens/
│   ├── _colors.scss
│   ├── _spacing.scss
│   ├── _typography.scss
│   ├── _sizes.scss
│   ├── _radii.scss
│   ├── _shadows.scss
│   ├── _breakpoints.scss
│   ├── _transitions.scss
│   └── _z-index.scss
├── themes/
│   ├── default.scss
│   └── dark.scss
├── _mixins.scss
├── _reset.scss
└── main.scss
```

Компоненты используют semantic variables:

```scss
.pt-table {
    color: var(--color-text);
    background: var(--color-surface);
    border-color: var(--color-border);
}
```

Правила:

* raw brand hex-цвета запрещены в компонентах;
* static inline styles запрещены;
* сложные компоненты используют scoped SCSS;
* классы сложных компонентов оформляются в BEM-стиле;
* focus-visible должен оставаться видимым;
* должны быть состояния hover, active, focus, disabled, invalid и loading;
* theme changes выполняются через theme tokens;
* public и admin зоны используют одну систему токенов.

## 10. Layouts и middleware

Публичная часть использует `layouts/default.vue`.

Админка использует `layouts/admin.vue`.

Маршруты `/admin` защищаются через `admin-auth.ts`.

Middleware должен:

* проверить admin session;
* загрузить текущего администратора;
* перенаправить неавторизованного пользователя на `/admin/login`;
* сохранить intended route;
* не полагаться только на скрытие кнопок;
* корректно обрабатывать 401 и 419.

## 11. Структура страниц

Публичная зона:

```text
pages/
├── index.vue
├── lists/index.vue
├── lists/[id].vue
└── lists/[id]/tasks/[taskId].vue
```

Админская зона:

```text
pages/admin/
├── login.vue
├── index.vue
├── users.vue
├── users/[id].vue
└── tasks.vue
```

Страницы должны оркестрировать данные и компоненты. Визуальную сложность и бизнес-операции нужно
выносить в `Pt*`, `Tm*`, composables и API layer.

## 12. Правила переноса Navision-кода

Перед переносом каждого компонента пройти чеклист:

1. Удалить JavaScript и переписать props, emits и slots на TypeScript.
2. Удалить Tailwind-классы.
3. Перенести визуальные значения в SCSS или semantic tokens.
4. Заменить Navision API и domain-specific данные на ToDoTest contracts.
5. Проверить SSR и client-only зависимости.
6. Добавить keyboard и screen reader support.
7. Добавить unit-тест на основное поведение.
8. Проверить lint, format, typecheck и test.

Приоритет портирования:

1. `UiTable`, `UiButton`, `UiInput`, `UiBadge`, `UiSpinner`.
2. `UiCard`, `UiEmpty`, `UiPagination`.
3. `UiSheet`, `UiModal`, `UiPopover`.
4. `ElInput`, `ElSelect`, `ElForm`, `ElInputWrapper`.
5. `PtTable`.
6. `PtFilter` и filter controls.
7. `PtTabs`.
8. `PtCardInfo`.
9. `TmSidebar`, `TmHeader`, `TmMenu`.
10. Todo и admin feature templates.

## 13. Что не переносить из Navision

Не переносить напрямую:

* Bearer-auth и `useAuth`;
* `useLibrary`;
* Navision-specific status maps;
* Navision business sheets;
* Tailwind config и Tailwind classes;
* JavaScript-only helpers;
* shadcn-specific dependencies без отдельного решения;
* глобальные ошибки, silent catches и неtyped response handling;
* accessibility-ограничения старого `UiSheet`.

Переносится структура и поведение, а API, состояние, стили и доменная логика реализуются заново
под ToDoTest.

## 14. Порядок реализации

### Этап 1. UX и архитектура

* согласовать карту экранов;
* определить public и admin navigation;
* описать loading, empty, error, blocked и archived состояния;
* определить список shared компонентов;
* утвердить desktop и mobile поведение;
* зафиксировать прототипы главных экранов.

### Этап 2. Frontend foundation

* создать `layouts`;
* создать route middleware;
* создать typed API client;
* создать типы ресурсов и ошибок;
* адаптировать базовые `Ui*` компоненты;
* перенести tokens и проверить темы;
* подготовить `PtTable`, `PtFilter`, `PtTabs` и `UiSheet`.

### Этап 3. Демо-профили

* список активных профилей;
* создание профиля;
* сохранение `user_id`;
* восстановление профиля;
* обработка заблокированного профиля;
* explicit demo mode indicator.

### Этап 4. Todo-списки

* список активных и архивных списков;
* tabs через `PtTabs`;
* создание списка;
* переименование;
* архивирование;
* восстановление;
* read-only состояние архивного списка.

### Этап 5. Задачи и теги

* `PtTaskCard` или `PtTable` для задач;
* создание и редактирование задачи;
* status, priority и due date;
* tag select и управление тегами;
* поиск и фильтры;
* sorting и pagination;
* overdue state;
* 422 field errors;
* read-only archived и blocked states.

### Этап 6. Админка

* admin login;
* CSRF flow;
* admin layout;
* admin middleware;
* dashboard;
* users table;
* block/unblock;
* user details;
* lists archive/restore;
* task table;
* edit task;
* confirm and hard delete task.

### Этап 7. Качество

* unit-тесты для API mapper, pagination, filters и form errors;
* component-тесты для таблицы, filters и sheet;
* Playwright public flow;
* Playwright admin smoke flow;
* responsive проверка;
* accessibility проверка;
* lint;
* format check;
* typecheck;
* production build.

После каждого TODO остановиться, предоставить отчёт и дождаться подтверждения пользователя.

## 15. CI и локальные проверки

Обязательные frontend-команды:

```bash
npm run lint
npm run format:check
npm run typecheck
npm run test:run
npm run test:e2e
npm run build
```

`make check` должен продолжать запускать полный набор проверок через Docker.

CI использует Node.js 24.18 и должен проверять frontend отдельно по quality, test и build stages.

## 16. Критерии готовности frontend-среза

Frontend-срез считается готовым, если:

* API-вызовы идут только через typed API layer;
* компоненты используют правильный префикс `Ui`, `El`, `Pt` или `Tm`;
* стили используют SCSS tokens;
* loading, empty, error и disabled состояния реализованы;
* 422 ошибки привязаны к полям формы;
* 403 состояния отображают read-only режим;
* mutation после успеха обновляет локальную модель данными Resource;
* для delete обработан HTTP 204;
* добавлены соответствующие unit-тесты;
* главный сценарий покрыт Playwright;
* lint, format, typecheck, tests и build проходят.

## 17. Главный архитектурный принцип

```text
Ui   отвечает за визуальный примитив
El   объединяет универсальные элементы
Pt   реализует повторяемый интерфейсный паттерн
Tm   собирает крупный экран или layout
Page связывает маршрут, данные и шаблон
API  изолирует transport и response contracts
Feature содержит доменную логику ToDoTest
```

Так сохраняется узнаваемая архитектура Navision с `PtTable`, `TmSidebar` и связанными слоями,
при этом реализация остаётся совместимой с TypeScript, SCSS, strict typecheck, cookie-сессиями,
typed API, тестами и CI ToDoTest.
