# Промпт для Claude Design

Ты создаёшь дизайн фронтенда для проекта ToDoTest. У тебя нет доступа к репозиторию, поэтому все решения принимай на основе этого описания.

Твоя задача: разработать цельную дизайн-систему, UX-структуру и набор экранов, которые можно напрямую реализовать в Nuxt 3, Vue 3, TypeScript и SCSS.

## 1. Контекст продукта

ToDoTest является небольшим приложением для управления задачами.

Пользователь может:

- выбрать или восстановить демо-профиль;
- создавать todo-списки;
- переименовывать, архивировать и восстанавливать списки;
- создавать и редактировать задачи;
- назначать статусы, приоритеты, сроки и теги;
- искать, фильтровать и сортировать задачи;
- просматривать активные и архивные списки;
- работать с read-only состояниями.

В приложении также существует отдельная административная зона `/admin`.

Администратор может:

- войти через cookie-сессию;
- просматривать dashboard;
- просматривать пользователей и их профили;
- управлять todo-списками;
- просматривать и редактировать задачи;
- блокировать и разблокировать пользователей;
- работать с архивными и заблокированными сущностями;
- удалять задачи после явного подтверждения.

## 2. Приоритеты

Соблюдай следующие приоритеты:

1. Функциональность, понятная UX-структура и accessibility.
2. Технические ограничения ToDoTest.
3. Визуальная система Refero.
4. Компонентные паттерны Navision.

Refero задаёт визуальный язык. Navision задаёт подход к переиспользуемым компонентам. ToDoTest задаёт архитектуру и технические ограничения.

## 3. Визуальный референс

Используй эту дизайн-систему как обязательный визуальный референс:

https://styles.refero.design/style/efcb73cb-b84a-4ae7-9a2b-e1116f79f130

Используй следующие характеристики:

- белый canvas;
- контрастная монохромная база;
- плотный productivity-интерфейс;
- таблицы, списки, tabs, фильтры и status pills;
- компактные контролы;
- выразительная типографика;
- тонкие границы;
- аккуратные avatar-группы;
- pill-формы для buttons, tabs, tags и badges;
- фиолетовый и синий как контролируемые акценты;
- минимум декоративных эффектов;
- визуальная иерархия, ориентированная на рабочий процесс.

Не используй логотип, название, тексты, иллюстрации или proprietary assets ClickUp. Перенеси визуальные принципы Refero в продукт ToDoTest.

Не превращай приложение в маркетинговый лендинг. Рабочие экраны должны выглядеть как современный B2B productivity tool.

## 4. Типографика

Используй:

- Plus Jakarta Sans для заголовков и крупных UI-элементов;
- Inter для основного текста и интерфейсных подписей;
- Sometype Mono или JetBrains Mono для технических значений, идентификаторов и metadata.

Рекомендуемая шкала:

- body small: 14px;
- body: 16px;
- subheading: 20px;
- heading small: 34px;
- heading: 48px;
- display: 60px;
- display large: 80px.

Для dashboard, таблиц, списков и admin screens используй преимущественно заголовки 34-48px.

Размеры 60-80px допускаются для profile selection, welcome screens и крупных брендовых акцентов.

Заголовки должны быть плотными:

- font-weight: 650-800;
- letter-spacing около -0.04em.

## 5. Палитра

Используй semantic design tokens. Не размещай raw hex-цвета внутри компонентов.

Рекомендуемая палитра:

```scss
--color-primary: #202020;
--color-primary-hover: #2a2a2a;

--color-brand: #6647f0;
--color-brand-hover: #5538d9;

--color-link: #0091ff;
--color-link-hover: #007ad9;

--color-surface: #ffffff;
--color-surface-muted: #f8f9fa;
--color-surface-subtle: #e9ebf0;

--color-text: #202020;
--color-text-strong: #090c1d;
--color-text-muted: #646464;
--color-text-subtle: #838383;

--color-border: #e8e8e8;
--color-border-strong: #d4d4d4;

--color-success: #00c07a;
--color-info: #0091ff;
--color-warning: #f59e0b;
--color-danger: #d92d20;

--color-focus: #0091ff;
```

Правила использования:

- #202020 используется для primary CTA и основных заголовков;
- #6647f0 используется для brand-акцентов, выбранных состояний и важных identity-моментов;
- #0091ff используется для ссылок, focus-состояний и вторичных интерактивных действий;
- #00c07a используется для успешных состояний;
- #e8e8e8 используется для стандартных границ;
- большие текстовые блоки и фоны не должны использовать pure black #000;
- цвета должны быть доступны по контрасту WCAG AA.

## 6. Радиусы, spacing и плотность

Используй:

- input radius: 9px;
- card radius: 12px;
- image radius: 16px;
- large card radius: 20px;
- buttons, tabs, badges, tags и chips: 9999px.

Spacing-система строится на шаге 4px.

Рекомендуемые значения:

- element gap: 12px;
- card padding: 28px;
- section gap: 80px;
- максимальная ширина контента: 1200px.

Интерфейс должен быть плотным и хорошо работать с большими списками данных.

## 7. Основные экраны

Разработай следующие экраны и состояния.

### Публичная зона

1. Profile selection

- список демо-профилей;
- карточка профиля;
- статус профиля;
- заблокированный профиль;
- восстановление ранее выбранного профиля;
- loading, empty и error states.

2. Todo lists

- список активных todo-списков;
- tabs для active и archived;
- создание списка;
- переименование;
- архивирование;
- восстановление;
- пустое состояние;
- read-only состояние.

3. Todo list details

- заголовок списка;
- количество задач;
- фильтры;
- поиск;
- сортировка;
- таблица или плотный список задач;
- pagination;
- tags;
- priority;
- status;
- due date.

4. Task creation and editing

Используй sheet или modal:

- название задачи;
- описание;
- статус;
- приоритет;
- срок;
- теги;
- сохранение;
- отмена;
- validation errors;
- submitting state;
- success state;
- error state.

5. Task details

- детальная информация;
- история или metadata при необходимости;
- read-only режим для архивного списка;
- подтверждение безвозвратного удаления.

### Административная зона

1. Admin login

- email;
- password;
- validation;
- loading;
- invalid credentials;
- expired session;
- CSRF-related error state.

2. Admin dashboard

- summary cards;
- количество пользователей;
- количество списков;
- количество задач;
- заблокированные пользователи;
- последние изменения;
- быстрые переходы.

3. Admin users

- таблица пользователей;
- поиск;
- фильтрация;
- сортировка;
- pagination;
- status badges;
- блокировка и разблокировка;
- user sheet.

4. Admin user details

- профиль пользователя;
- todo-списки;
- задачи;
- blocked state;
- read-only state;
- действия администратора.

5. Admin tasks

- таблица задач;
- фильтры;
- сортировка;
- status;
- priority;
- tags;
- due date;
- edit sheet;
- delete confirmation.

## 8. UI-kit и компоненты

Покажи отдельный UI-kit со следующими компонентами:

- Button;
- Input;
- Textarea;
- Select;
- Checkbox;
- Switch;
- Badge;
- Tag;
- Card;
- Table;
- Pagination;
- Tabs;
- Sheet;
- Modal;
- Popover;
- Dropdown;
- Tooltip;
- Calendar;
- Toast;
- Spinner;
- Empty state;
- Error state;
- Avatar;
- Avatar group.

Для каждого важного компонента покажи:

- default;
- hover;
- active;
- focus-visible;
- selected;
- disabled;
- loading;
- invalid;
- error;
- success;
- mobile state.

## 9. Именование компонентов

Сохрани паттерн именования Navision.

### `Ui*`

Низкоуровневые визуальные примитивы:

- `UiButton`;
- `UiInput`;
- `UiSelect`;
- `UiBadge`;
- `UiTable`;
- `UiSheet`;
- `UiModal`;
- `UiPagination`;
- `UiEmpty`.

### `El*`

Составные универсальные элементы:

- `ElForm`;
- `ElInput`;
- `ElSelect`;
- `ElInputCalendar`;
- `ElInputWrapper`;
- `ElLink`.

### `Pt*`

Переиспользуемые интерфейсные паттерны:

- `PtTable`;
- `PtFilter`;
- `PtTabs`;
- `PtCardInfo`;
- `PtTaskCard`;
- `PtTaskFilters`;
- `PtListCard`;
- `PtUserProfile`.

### `Tm*`

Крупные templates, layouts и sheets:

- `TmHeader`;
- `TmSidebar`;
- `TmMenu`;
- `TmTodoListSheet`;
- `TmTaskSheet`;
- `TmAdminDashboard`;
- `TmAdminUserSheet`;
- `TmAdminTaskSheet`.

Feature-компоненты должны использовать тот префикс, который отражает их слой и ответственность. `Feature*` применяй только для действительно изолированной domain feature. Для reusable patterns используй `Pt*`, для крупных экранных блоков и sheets используй `Tm*`.

Файлы компонентов используют PascalCase:

```text
PtTable.vue
TmSidebar.vue
UiButton.vue
```

В Vue-шаблонах используются kebab-case:

```vue
<pt-table />
<tm-sidebar />
<ui-button />
```

## 10. Структура фронтенда

Дизайн должен быть совместим со следующей структурой:

```text
frontend/
├── app.vue
├── pages/
├── layouts/
│   ├── default.vue
│   └── admin.vue
├── middleware/
│   ├── profile.ts
│   └── admin-auth.ts
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

Разделение ответственности:

- `components/ui` содержит примитивы;
- `components/elements` содержит универсальные composed elements;
- `components/patterns` содержит reusable patterns;
- `components/templates` содержит крупные экранные блоки;
- `pages` только собирают экран;
- `layouts` отвечают за public и admin shell;
- `middleware` отвечает за доступ к маршрутам;
- `services/api` является typed API layer;
- `composables` содержат переиспользуемое поведение;
- `utils` содержат чистые функции.

## 11. Навигация и layouts

Используется одно Nuxt-приложение.

Публичная зона:

```text
layouts/default.vue
```

Административная зона:

```text
layouts/admin.vue
```

Admin shell должен включать:

- sidebar;
- header;
- текущий раздел;
- user/admin menu;
- breadcrumbs или page title;
- responsive navigation;
- active route state;
- collapsed sidebar state.

Покажи различие между public navigation и admin navigation через layout, плотность, sidebar и визуальную иерархию.

## 12. Поведение компонентов

Все компоненты должны быть спроектированы через:

- props;
- emits;
- slots;
- typed configuration;
- controlled state;
- явные loading и error states.

UI-компоненты не должны зависеть от domain API.

Для `PtTable` предусмотрен typed contract:

```ts
interface TableColumn<Row> {
    field: string
    header: string
    sortable?: boolean
    align?: 'left' | 'center' | 'right'
    value?: (row: Row) => unknown
    format?: (value: unknown, row: Row) => string
}
```

`PtTable` должен поддерживать:

- typed columns;
- sorting;
- pagination;
- loading;
- empty state;
- row actions;
- slots для специальных ячеек;
- mobile presentation.

`PtFilter` должен поддерживать:

- input;
- select;
- multi-select;
- date;
- button-group;
- debounce;
- query synchronization;
- reset;
- active filter summary.

`PtTabs` должен поддерживать:

- model-backed tabs;
- route-backed tabs;
- active state;
- disabled tabs;
- mobile overflow.

`UiSheet` и `UiModal` должны поддерживать:

- `v-model`;
- overlay;
- Escape;
- закрытие по клику вне панели;
- header, content и footer slots;
- confirm/cancel actions;
- nested panels;
- responsive width;
- focus trap;
- возврат фокуса на trigger;
- блокировку body scroll.

## 13. Состояния продукта

Для каждого экрана и запроса обязательно покажи:

- initial;
- loading;
- success;
- empty;
- error;
- disabled;
- submitting;
- deleting;
- read-only;
- archived;
- blocked.

Доменные правила:

- заблокированный профиль доступен для чтения;
- заблокированный профиль не может изменять данные;
- архивный список доступен для чтения;
- задачи архивного списка нельзя изменять;
- администратор может управлять задачами независимо от blocked и archived состояния;
- удаление задачи требует отдельного подтверждения;
- ошибки валидации должны быть привязаны к конкретным полям.

## 14. SCSS и design tokens

Дизайн должен быть реализуем через SCSS и CSS custom properties.

Структура tokens:

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

- raw colors запрещены внутри компонентов;
- static inline styles запрещены;
- spacing, radii и shadows берутся из tokens;
- сложные компоненты используют scoped SCSS;
- сложные классы используют BEM-style naming;
- global styles ограничиваются reset, typography defaults, tokens и utilities;
- public и admin используют одну token-систему;
- theme changes выполняются через theme tokens.

## 15. Accessibility

Обеспечь:

- WCAG AA contrast;
- visible keyboard focus;
- `focus-visible`;
- keyboard navigation для tabs, menus, dropdowns и dialogs;
- ARIA labels;
- правильные heading levels;
- доступные таблицы;
- focus trap в sheets и modals;
- возврат фокуса после закрытия overlay;
- понятные error messages;
- доступные disabled и read-only states;
- корректное поведение на мобильных устройствах.

## 16. Responsive behavior

Покажи минимум три состояния:

- desktop;
- tablet;
- mobile.

На мобильных устройствах:

- sidebar превращается в drawer;
- таблица может переходить в list/card presentation;
- filters открываются в sheet;
- action buttons остаются доступными;
- sheets занимают почти всю ширину;
- tabs поддерживают горизонтальный scroll;
- длинные значения не ломают layout;
- важные действия остаются видимыми.

## 17. Технические ограничения

Дизайн должен быть реализуем в следующем стеке:

- Nuxt 3;
- Vue 3;
- TypeScript strict;
- Composition API;
- `<script setup lang="ts">`;
- SCSS;
- CSS custom properties;
- typed API layer;
- Vitest;
- Playwright;
- ESLint;
- Prettier;
- CI quality checks.

В реализации проекта запрещены:

- JavaScript-only components;
- Tailwind;
- mixins;
- global event bus;
- hardcoded endpoint URLs в компонентах;
- raw brand colors в компонентах;
- необоснованные глобальные abstractions;
- silent error states.

## 18. API и данные

Визуальные компоненты должны быть подготовлены к typed API layer.

Endpoint paths будут находиться только в:

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

Используются отдельные состояния:

```text
useDemoProfile()
useAdminSession()
```

`useDemoProfile` отвечает за выбранный demo profile.

`useAdminSession` отвечает за cookie-сессию администратора, CSRF и middleware.

Bearer authentication и Navision `useAuth` в ToDoTest не используются.

## 19. Тестируемость

Дизайн должен позволять покрыть тестами:

- выбор и восстановление профиля;
- создание todo-списка;
- переключение active/archived tabs;
- создание и редактирование задачи;
- фильтрацию и сортировку;
- pagination;
- read-only состояния;
- блокировку профиля;
- admin login;
- admin navigation;
- открытие и закрытие sheets;
- подтверждение удаления;
- loading, empty и error states.

Покажи сценарии, которые должны быть покрыты unit-тестами, component-тестами и Playwright e2e-тестами.

## 20. Формат результата

Сформируй результат в следующем порядке:

1. Краткое описание визуальной концепции.
2. Объяснение, как Refero адаптирован под ToDoTest.
3. UI-kit.
4. Design tokens.
5. Typography scale.
6. Color and semantic states.
7. Spacing, radii и shadows.
8. Public navigation.
9. Admin navigation.
10. Основные public screens.
11. Основные admin screens.
12. Responsive versions.
13. Empty, loading, error, disabled и read-only states.
14. Sheets, modals, tables, filters и tabs.
15. Component inventory с именами `Ui*`, `El*`, `Pt*` и `Tm*`.
16. Mapping компонентов на структуру frontend-папок.
17. Accessibility notes.
18. Test scenarios.
19. Краткие рекомендации по реализации через TypeScript и SCSS.

Каждое решение должно быть конкретным: показывай размеры, цвета, состояния, структуру блоков, hierarchy и component boundaries.

Не добавляй функциональность, которой нет в описании продукта, без явного объяснения причины.
