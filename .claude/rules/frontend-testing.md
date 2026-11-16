---
paths:
  - "frontend/tests/**"
  - "frontend/**/*.spec.ts"
  - "frontend/**/*.test.ts"
---

# Frontend-тесты

Unit (Vitest, `tests/unit/`): сортировка и форматтеры колонок, debounce и query-сериализация `PtFilter`, правила доступа blocked/archived → read-only, маппинг ошибок API, пагинация, ошибки форм.

Component: `UiSheet` (v-model, Esc, клик вне, focus trap, возврат фокуса), `UiModal` (confirm disabled до подтверждения), `UiTable` (loading/empty/error, `aria-sort`), `ElForm` (ошибка привязана к полю), `PtTabs` (клавиатура).

E2E (Playwright, `tests/e2e/`): выбор и восстановление профиля; создание списка; переключение active/archived; создание и редактирование задачи; фильтрация и сортировка; pagination; read-only; блокировка профиля; admin login; admin navigation; открытие/закрытие sheets; подтверждение удаления; loading/empty/error.

Селекторы в e2e — по роли и `data-testid`, не по классам.
