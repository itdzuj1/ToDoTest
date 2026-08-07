# Экраны

Максимальная ширина контента 1200px. Section gap 80px, card padding 28px, element gap 12px.

---

## Public — layouts/default.vue
Горизонтальный header 56px: бренд (24px квадрат, radius 7, #202020, буква T) → пункты меню 14/500 с активным подчёркиванием 2px #202020 → справа профиль-чип (pill, border 1px #e8e8e8, аватар 26px, имя 13/500, «▾»). Без sidebar. Контент 1200px, паддинг 32–48px.

### 1. Profile selection — `pages/index.vue`
- Кикер mono 12px uppercase ls 0.08em #838383 — «Шаг 1 из 1».
- H1 80/0.95, ls −0.045em, weight 800, #090c1d — «Выберите профиль».
- Подзаголовок 16px #646464, max-width 560px.
- Блок восстановления: карточка max-width 560px, padding 12/16, border 1px #d4d4d4, radius 12, фон #f8f9fa; аватар 32px, заголовок 14/600, mono 12px «последний сеанс · дата», справа brand-кнопка 32px «Восстановить».
- Сетка профилей: 4 колонки, gap 12.
  - **default**: border 1px #e8e8e8, radius 12, padding 20; аватар 44px; имя 20/700 ls −0.02em; mono 12 #838383 «demo-02 · 2 списка · 11 задач»; badge «Активен» (success).
  - **selected**: border 2px #6647f0, фон #f0ecfe, badge «Выбран» белый на brand-тексте #4a2fc4.
  - **blocked**: фон #f8f9fa, серый аватар, badge «Заблокирован» (danger), подпись «Доступен только для чтения».
  - **loading**: skeleton — круг 44px и две полосы 14/10px, radius 9999, #eef0f3.
- **empty**: dashed-карточка, 20/650 «Демо-профилей нет» + подпись.
- **error**: border+фон danger-tint, заголовок 20/650 #a4231a, текст с кодом («GET /api/profiles — 503»), кнопка-outline «Повторить».

### 2. Todo lists — `pages/lists/index.vue`
- H1 48/1, ls −0.04em, 750 — «Мои списки»; подпись 14 #646464 «12 активных · 5 в архиве».
- Справа: поиск-pill 36px (min-width 200) + primary-кнопка 36px «+ Новый список».
- PtTabs: pill-контейнер padding 3, фон #f8f9fa, border 1px #e8e8e8; активный — #202020/белый 30px; счётчик opacity .6; disabled — opacity .45.
- Сетка карточек 3 колонки, gap 12:
  - **default**: 20/700 название, 13 #646464 «14 задач · 6 выполнено», прогресс-полоса 4px radius 9999 (#e9ebf0 / заливка #00c07a), низ: mono 12 «upd 07.08» + badge «Активный» (info).
  - **rename (inline)**: input 36px с focus-ring, подпись «Enter — сохранить · Esc — отменить», кнопки 30px Сохранить / Отмена.
  - **archived**: фон #f8f9fa, заголовок #646464, замок, кнопка «Восстановить», badge «Архив» (neutral).
- **blocked-баннер**: полоса warning (border #f59e0b, фон #fdf1de, текст #7c5108) — «Профиль заблокирован. Данные доступны только для чтения».

### 3. Todo list details — `pages/lists/[id].vue`
- Хлебные крошки в header 13px.
- H1 34/1.05 ls −0.035em 700 + подпись «14 задач · 6 выполнено · 2 просрочены».
- Действия: secondary «Архивировать», primary «+ Новая задача» (36px).
- **PtTaskFilters** — ряд pill-контролов 34px, снизу border-bottom 1px: поиск (min-width 240), Статус ▾, Приоритет (активный = border #6647f0 / фон #f0ecfe / текст #4a2fc4 / «×»), Теги ▾, Срок (mono), справа Сортировка.
- **PtTable**: обёртка border 1px radius 12 overflow hidden. Header 40px, фон #f8f9fa, 12/600 #646464. Строка 48px, border-bottom 1px. Колонки: чекбокс 36px / Задача 2.4fr / Статус 1fr / Приоритет 1fr / Срок 1fr / Теги 1.1fr / ⋯ 40px.
  - приоритет — точка 6px (#d92d20 / #f59e0b / #838383) + подпись 13px;
  - срок mono 13px, просроченный — #d92d20;
  - выполненная строка — line-through + #646464;
  - выделенная строка — фон #f0ecfe, border чекбокса #6647f0.
- Pagination снизу: «1–4 из 14» слева, pill-кнопки 32px справа, активная — #202020/белый.

### 4. TmTaskSheet — создание/редактирование
Правый sheet 480px, фон #fff, shadow lg, поверх overlay rgba(9,12,29,.35).
- Header 24px: mono-кикер «TmTaskSheet · TASK-1042», заголовок 24/700 ls −0.03em, «×».
- Поля (gap 16): Название* (input 40px), Описание (textarea 76px), Статус + Приоритет (2 колонки), Срок* (date), Теги (chips-инпут min-height 40, чипы 24px pill).
- Ошибка поля: border #d92d20 + подпись 12/500 #d92d20 под полем.
- Footer 16/24 на фоне #f8f9fa, border-top: primary 40px («Сохранить» / submitting — спиннер 13px + «Сохранение…»), secondary «Отмена», справа ghost-danger «Удалить».
- Поведение: v-model, Esc, клик вне, focus trap, возврат фокуса на trigger, scroll lock, ширина на мобильном ≈ 94%.

### 5. Task details — `pages/lists/[id]/tasks/[taskId].vue`
- Ряд бейджей: «Архивный список» (neutral) + статус + mono ID.
- H1 34/1.1 700; описание 16/1.6 #646464 max-width 560.
- Мета-полоса: 4 колонки (Приоритет, Срок, Создана, Обновлена) между двумя рулами 1px; подпись 12 #838383, значение 14/600 (даты — mono).
- Read-only полоса: фон #f8f9fa, «🔒 Список в архиве — редактирование недоступно» + ссылка «Восстановить список».
- **Удаление (UiModal)**: карточка 380px, radius 20, shadow lg, padding 24; заголовок 20/700; текст с mono-ID; чекбокс «Понимаю последствия»; кнопки Удалить (danger, disabled до чекбокса) / Отмена 40px.

---

## Admin — layouts/admin.vue
Sidebar 240px (collapsed 64px), фон #202020: бренд-квадрат с brand-заливкой, пункты 32px pill — активный `rgba(255,255,255,.12)`/белый, остальные `rgba(255,255,255,.62)`; внизу admin-чип через border-top `rgba(255,255,255,.12)`. Header 48–56px: breadcrumbs 12px, поиск-pill 32px, admin-меню. Контент padding 20–24px. Плотность на шаг выше public.

### 6. Admin login — `pages/admin/login.vue`
Кикер mono «ToDoTest Admin», H1 60/0.98 ls −0.045em 800 «Вход», форма max-width 360: Email, Пароль (invalid → «Неверный email или пароль»), primary-кнопка 40px. Отдельные состояния: warning-полоса «Сессия истекла…» с возвратом на исходный маршрут; danger-полоса «Ошибка CSRF-токена…».

### 7. Admin dashboard — `pages/admin/index.vue`
H1 34px «Обзор». Summary-карточки 2×2 (border 1px, radius 12, padding 14): подпись 12 #838383, число 34/750 ls −0.04em, дельта 12/600 (#00c07a или #838383). Карточка «Заблокированные» — danger-tint, число #a4231a, ссылка «Разобрать →». Ниже таблица «Последние изменения»: header 36px, строки 40px, mono-время 11px + текст 13px.

### 8. Admin users — `pages/admin/users.vue`
H1 34px + «248 всего · 7 заблокировано». Фильтры: Статус ▾, Сортировка ▾ (32px).
Таблица: header 36px, строка 44px, колонки `1.7fr 1.5fr 0.6fr 0.6fr 1.3fr 168px` — Пользователь (аватар 24px + имя 600), Email (mono 12 #646464), Списки, Задачи, Статус (badge), Действия (две pill-кнопки 26px: «Открыть» + «Блок»/«Разблок»; «Разблок» — заливка #202020).
Строка hover — фон #f8f9fa; loading — skeleton-полосы. Pagination 28px.

### 9. TmAdminUserSheet — `/admin/users/[id]`
Панель 400px справа, border-left. Header: аватар 44px, имя 20/700, mono «demo-03 · igor@demo.dev», бейджи «Заблокирован» + «read-only для пользователя». Тело: «Списки (1)» карточкой (название + badge «Архив» + «5 задач · обновлён …»), «Последние задачи» строками 8/10 с mono-датой, пояснение «Администратор может редактировать задачи заблокированного пользователя и архивных списков». Футер: primary «Разблокировать» + «Закрыть» (36px).

### 10. Admin tasks — `pages/admin/tasks.vue`
Фильтры: поиск, Пользователь ▾, Статус ▾, активный чип «Архивные списки ×».
Таблица: колонки `100px 2fr 1.2fr 1fr 0.9fr 60px` — ID (mono 12), Задача (+ мини-бейдж «архив» 18px), Пользователь, Статус, Срок (mono), ⋯. Строка в состоянии **deleting** — opacity .6, badge «Удаление…» (danger), спиннер вместо ⋯.
**Подтверждение удаления**: модал с вводом ID задачи для подтверждения, кнопки Удалить (danger) / Отмена 36px.

---

## Responsive
Брейкпойнты: sm 480 · md 768 · lg 1024 · xl 1280.
- **< 1024** — sidebar сворачивается в иконки 64px.
- **< 768** — sidebar → drawer по «☰»; таблица → карточки (PtTaskCard); фильтры → bottom-sheet 88% высоты с «ручкой» 40×4; tabs — горизонтальный скролл, чипы `white-space:nowrap`; sheets на ≈94% ширины; основное действие — sticky-кнопка 48px внизу; тач-цели ≥ 44px; длинные значения обрезаются через `text-overflow:ellipsis`, а не ломают сетку.
