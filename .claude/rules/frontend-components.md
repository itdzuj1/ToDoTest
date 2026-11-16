---
paths:
  - "frontend/components/**"
  - "frontend/pages/**"
  - "frontend/layouts/**"
---

# Компоненты, состояния и accessibility

Каждый компонент проектируется через props, emits, slots, typed configuration, controlled state и явные loading/error состояния. Полные сигнатуры — в `docs/components.md`.

## Контракты паттернов

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

- `PtTable` — typed columns, сортировка, пагинация, loading, empty, row actions, слоты `cell:<field>`, мобильное представление карточками.
- `PtFilter` — типы `input`, `select`, `multi-select`, `date`, `button-group`; debounce, синхронизация с query, reset, сводка активных фильтров. Фильтры задач: `search`, `status`, `priority`, `tag_id`, `due`, `sort`, `direction`, `page`, `per_page`.
- `PtTabs` — model-backed и route-backed режимы, active/disabled, горизонтальный overflow на мобильном.
- `PtCardInfo` — typed information rows со слотами.
- `UiSheet` / `UiModal` — `v-model`, overlay, Esc, клик вне панели, слоты header/content/footer, confirm/cancel, вложенные панели, адаптивная ширина, focus trap, возврат фокуса на trigger, блокировка scroll body.
- Общее поведение: `useTableData` (query-состояние таблицы и пагинация), `useClickOutside` (dropdown, popover).

## Состояния

Для каждого экрана и запроса: initial, loading, success, empty, error, disabled, submitting, deleting, read-only, archived, blocked.

- loading — skeleton, геометрия не «прыгает»;
- error — сообщение с кодом + «Повторить»; «тихие» ошибки запрещены;
- submitting — спиннер в кнопке, поля disabled, повторный submit невозможен;
- read-only / archived — контролы видимы, но disabled + замок и одна поясняющая полоса;
- HTTP 403 даёт явное read-only состояние там, где этого требует контракт.

## Доменные правила UI

1. Заблокированный профиль доступен для чтения, мутации отключены.
2. Архивный список доступен для чтения; его задачи изменять нельзя.
3. Администратор управляет задачами независимо от blocked и archived.
4. Удаление задачи — только после явного подтверждения.
5. Ошибки валидации привязаны к конкретному полю.

## Accessibility (WCAG AA)

- контраст текста ≥ 4.5:1;
- `:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 2px }` — не снимать;
- таблицы: `th[scope]`, `aria-sort`, скрытый caption;
- клавиатура для tabs (roving tabindex, ← → Home End), меню, dropdown, dialog;
- sheets/modals: `role="dialog" aria-modal`, focus trap, возврат фокуса, scroll lock;
- строгая иерархия заголовков, один `h1` на страницу;
- ошибки полей: `aria-invalid` + `aria-describedby`; тосты в `aria-live`;
- тач-цели ≥ 44px на мобильных;
- каждый интерактивный элемент получает стабильный `data-testid`.
