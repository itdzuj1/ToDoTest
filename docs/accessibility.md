# Accessibility (WCAG AA)

- **Контраст** ≥ 4.5:1 для текста. Текст на tint-фонах берётся тёмным: success `#046c4a`, info `#0060a9`, warning `#96620a`, danger `#a4231a`, brand `#4a2fc4`. Крупные заголовки и чипы ≥ 3:1.
- **Focus:** `:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 2px; }` — никогда не снимается, в том числе у pill-кнопок и строк таблицы.
- **Таблицы:** `<table>` с `th[scope="col"]`, `aria-sort` на сортируемых колонках, визуально скрытый `<caption>`, чекбокс строки с `aria-label`.
- **Tabs:** `role="tablist"`, roving tabindex, ← → Home End, `aria-selected`, `aria-controls`.
- **Dropdown / Popover / Menu:** открытие Enter/Space/↓, навигация стрелками, закрытие Esc с возвратом фокуса на trigger, `aria-expanded`.
- **Sheet / Modal:** `role="dialog" aria-modal="true"`, `aria-labelledby` на заголовок, focus trap, возврат фокуса, блокировка скролла body, Esc.
- **Заголовки:** строгая иерархия h1→h2→h3, один h1 на страницу.
- **Ошибки:** `aria-invalid="true"` + `aria-describedby` на текст ошибки; тосты в контейнере `aria-live="polite"`, критичные — `assertive`.
- **Disabled / read-only:** для read-only использовать `readonly`/`aria-disabled`, чтобы элемент оставался в фокусируемом потоке и объяснял причину.
- **Mobile:** тач-цели ≥ 44px, drawer с focus trap, скролл-таблы доступны с клавиатуры (`tabindex="0"` на скролл-контейнере).
