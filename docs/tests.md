# Тестовые сценарии

## Unit (Vitest)
- сортировка и `format`/`value` колонок `TableColumn`
- `PtFilter`: debounce, сериализация и восстановление из query, reset
- утилиты дат и статусов (просрочено / выполнено)
- правила доступа: `blocked` или `archived` → `readonly === true`
- `services/api`: маппинг ошибок в типизированные `ApiError`

## Component (Vitest + Vue Test Utils)
- `UiSheet`: v-model, Esc, клик вне панели, focus trap, возврат фокуса на trigger, scroll lock
- `UiModal`: confirm disabled до подтверждения (чекбокс / ввод ID)
- `UiTable`: слоты loading / empty / error, `aria-sort`, выбор строк
- `ElForm` + `ElInputWrapper`: ошибка привязана к полю через `aria-describedby`
- `PtTabs`: клавиатурная навигация, disabled-таб не активируется
- `UiButton`: loading блокирует повторный click

## E2E (Playwright)
1. выбор демо-профиля и восстановление предыдущего
2. создание списка → переименование → архивирование → восстановление
3. переключение табов active/archived (в т.ч. через прямой URL)
4. создание задачи, ошибка валидации срока, успешное сохранение, тост
5. фильтрация + сортировка + pagination с сохранением состояния в query
6. read-only: архивный список — контролы disabled, попытка правки невозможна
7. blocked-профиль: баннер, мутации недоступны
8. admin login: неверные креды, истёкшая сессия, CSRF-ошибка
9. admin navigation: sidebar collapsed, breadcrumbs, активный маршрут
10. блокировка и разблокировка пользователя из таблицы и из sheet
11. удаление задачи админом с подтверждением
12. loading / empty / error состояния через мок-ответы API
13. mobile 375: drawer, карточки вместо таблицы, фильтры в sheet
