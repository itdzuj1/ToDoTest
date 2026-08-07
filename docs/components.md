# Контракты компонентов

Общие правила: только `props / emits / slots`, controlled state, никаких обращений к API из `Ui*`. Все размеры и цвета — из токенов.

## components/ui — примитивы
Полный список: UiButton, UiInput, UiTextarea, UiSelect, UiCheckbox, UiSwitch, UiBadge, UiTag, UiCard, UiTable, UiPagination, UiTabs, UiSheet, UiModal, UiPopover, UiDropdown, UiTooltip, UiCalendar, UiToast, UiSpinner, UiEmpty, UiError, UiAvatar, UiAvatarGroup.

```ts
// UiButton
interface UiButtonProps {
  variant?: 'primary' | 'brand' | 'secondary' | 'ghost' | 'danger' // default 'primary'
  size?: 'sm' | 'md' | 'lg'        // 32 / 36 / 40 px
  loading?: boolean
  disabled?: boolean
  block?: boolean
  type?: 'button' | 'submit'
}
// emits: (e: 'click', ev: MouseEvent)
// slots: default, icon-left, icon-right
```

```ts
// UiInput / UiTextarea
interface UiInputProps {
  modelValue: string | number | null
  placeholder?: string
  invalid?: boolean
  disabled?: boolean
  readonly?: boolean
  size?: 'sm' | 'md'
}
// emits: 'update:modelValue', 'blur', 'focus'
```

```ts
// UiBadge / UiTag
interface UiBadgeProps {
  tone?: 'neutral' | 'success' | 'info' | 'warning' | 'danger' | 'brand'
  size?: 'sm' | 'md'   // 22 / 24 px, radius pill
}
// UiTag дополнительно: removable?: boolean; emits 'remove'
```

```ts
// UiTable — «глупая» таблица; сортировку и пагинацию контролирует родитель
interface TableColumn<Row> {
  field: string
  header: string
  sortable?: boolean
  align?: 'left' | 'center' | 'right'
  width?: string                 // css grid track, напр. '1.3fr' | '168px'
  value?: (row: Row) => unknown
  format?: (value: unknown, row: Row) => string
}
interface UiTableProps<Row> {
  columns: TableColumn<Row>[]
  rows: Row[]
  rowKey: keyof Row | ((row: Row) => string)
  loading?: boolean
  skeletonRows?: number          // default 5
  selectable?: boolean
  selected?: string[]
  sort?: { field: string; dir: 'asc' | 'desc' } | null
  density?: 'default' | 'compact' // 48 / 44 px
}
// emits: 'update:sort', 'update:selected', 'row-click'
// slots: cell:<field>, actions, empty, error
```

```ts
// UiSheet / UiModal
interface UiOverlayProps {
  modelValue: boolean
  width?: string                 // sheet: '480px'; на мобильном 94vw
  closeOnOverlay?: boolean       // default true
  closeOnEsc?: boolean           // default true
  returnFocus?: boolean          // default true
}
// emits: 'update:modelValue', 'confirm', 'cancel'
// slots: header, default, footer
// поведение: role="dialog" aria-modal="true", focus trap, scroll lock, вложенные панели через стек z-index
```

```ts
// UiPagination
interface UiPaginationProps { page: number; pageSize: number; total: number; siblings?: number }
// emits: 'update:page', 'update:pageSize'
```

```ts
// UiEmpty / UiError
interface UiEmptyProps { title: string; description?: string; actionLabel?: string }
interface UiErrorProps { title: string; description?: string; code?: string | number; retryable?: boolean }
// emits: 'action' | 'retry'
```

## components/elements
`ElForm` (submit, валидация, disabled при submitting; emits `submit`), `ElInput`, `ElSelect`, `ElInputCalendar`, `ElInputWrapper` (label + hint + error, связывает `aria-describedby`), `ElLink`.

```ts
interface ElInputWrapperProps {
  label: string
  required?: boolean
  hint?: string
  error?: string | null      // наличие → aria-invalid у контрола
  for?: string
}
```

## components/patterns
```ts
// PtTable — UiTable + сортировка, пагинация, empty/error/loading, row actions, mobile→карточки
interface PtTableProps<Row> {
  columns: TableColumn<Row>[]
  rows: Row[]
  total: number
  page: number
  pageSize: number
  loading?: boolean
  error?: { message: string; code?: number } | null
  emptyTitle?: string
  mobile?: 'cards' | 'scroll'   // default 'cards'
}
// emits: 'update:page', 'update:sort', 'retry', 'row-action'
```

```ts
// PtFilter — конфигурируемая панель фильтров с синхронизацией в query
type FilterControl =
  | { kind: 'input'; field: string; placeholder?: string; debounce?: number }
  | { kind: 'select'; field: string; label: string; options: { value: string; label: string }[] }
  | { kind: 'multi-select'; field: string; label: string; options: { value: string; label: string }[] }
  | { kind: 'date'; field: string; label: string }
  | { kind: 'button-group'; field: string; options: { value: string; label: string }[] }

interface PtFilterProps {
  controls: FilterControl[]
  modelValue: Record<string, unknown>
  syncQuery?: boolean          // default true
  debounce?: number            // default 300
}
// emits: 'update:modelValue', 'reset'
// slots: summary (активные фильтры + счётчик «Сбросить (2)»)
```

```ts
// PtTabs
interface PtTabsProps {
  tabs: { value: string; label: string; count?: number; disabled?: boolean; to?: string }[]
  modelValue?: string          // model-backed
  routeKey?: string            // route-backed (query param)
}
// emits: 'update:modelValue'; мобильный overflow — горизонтальный скролл
```

Остальные: `PtCardInfo` (summary-карточка dashboard), `PtTaskCard` (мобильная карточка задачи), `PtTaskFilters` (PtFilter с конфигом задач), `PtListCard` (карточка списка + inline-переименование, emits `rename`/`archive`/`restore`), `PtUserProfile` (карточка демо-профиля, props `profile`, `selected`, `blocked`).

## components/templates
`TmHeader`, `TmSidebar` (`collapsed`, `items`, emits `toggle`), `TmMenu`, `TmTodoListSheet`, `TmTaskSheet` (props `taskId | null`, `readonly`; emits `saved`, `deleted`), `TmAdminDashboard`, `TmAdminUserSheet`, `TmAdminTaskSheet`.

## Границы слоёв
- `Ui*` — ноль знаний о домене и API.
- `El*` — форменная обвязка и a11y-связи, без домена.
- `Pt*` — знают доменные типы (Task, TodoList, Profile), но не эндпоинты.
- `Tm*` — владеют состоянием sheet/layout, собирают паттерны.
- `pages/` — только композиция + composables; эндпоинты только в `services/api`.
