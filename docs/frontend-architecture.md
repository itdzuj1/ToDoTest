# Анализ архитектуры фронтенда Navision

Источник анализа: `/Users/vlad/work/navision/frontend`.

Документ нужен как reference при создании фронтенда другого проекта.

## 1. Краткий вывод

Фронтенд является самостоятельным Nuxt-приложением внутри Laravel-проекта.

Главная формула архитектуры:

```text
страница = состояние маршрута + API-запрос + паттерн интерфейса + шаблон экрана
```

Основные характеристики:

* Nuxt 3 и Vue 3;
* JavaScript без TypeScript;
* Vue SFC с `<script setup>`;
* Tailwind CSS;
* shadcn-vue и reka-ui;
* lucide-vue-next для иконок;
* VueUse;
* vue-sonner для уведомлений;
* dayjs для работы с датами;
* Laravel API с Bearer-токеном.

В исходном фронтенде около 100 компонентов, 11 страниц и 8 composable-функций.

## 2. Структура папок

```text
frontend/
├── assets/
│   └── css/
├── components/
│   ├── ui/
│   ├── elements/
│   ├── patterns/
│   └── templates/
├── composables/
├── layouts/
├── lib/
├── middleware/
├── pages/
├── plugins/
├── public/
└── utils/
```

### `components/ui`

Базовые визуальные примитивы:

* `UiButton`;
* `UiInput`;
* `UiSelect`;
* `UiTable`;
* `UiCard`;
* `UiSheet`;
* `UiBadge`;
* `UiCalendar`;
* `UiAvatar`;
* `UiEmpty`;
* `UiSpinner`;
* `UiSwitch`.

Они отвечают за внешний вид, простое поведение и работу с низкоуровневыми библиотеками. Бизнес-логика в этот слой не помещается.

### `components/elements`

Составные универсальные элементы с бизнес-нейтральным смыслом:

* `ElInput`;
* `ElSelect`;
* `ElInputCalendar`;
* `ElMonth`;
* `ElForm`;
* `ElLogo`;
* `ElLink`;
* `ElIcon`;
* `ElThemeToggle`.

Например, `ElInput` добавляет к `UiInput` label, error, иконки и отступы.

### `components/patterns`

Повторяемые интерфейсные паттерны:

* `PtTable`;
* `PtFilter`;
* `PtCardInfo`;
* `PtTabs`;
* `PtUserProfile`.

Фильтры вынесены на отдельный уровень:

```text
PtFilter
├── PtFilterInput
├── PtFilterSelect
├── PtFilterInputCalendar
└── PtFilterButtonGroup
```

### `components/templates`

Крупные шаблоны экранов и доменные панели:

* `TmHeader`;
* `TmSidebar`;
* `TmMenu`;
* `TmSheetRemoteUser`;
* `TmSheetRemoteTransaction`;
* `TmSheetExcludedUser`;
* `TmSheetStatTransactions`.

В этом слое уже находится API-логика, загрузка данных и бизнес-сценарии.

### `pages`

Страницы в основном собирают экран, подключают composables и описывают конфигурацию таблиц.

Пример композиции:

```text
remote-users.vue
└── PtTable
    ├── PtFilter
    │   └── PtFilterSelect
    │       └── ElSelect
    │           └── UiSelect
    └── NuxtPage
        └── TmSheetRemoteUser
```

## 3. Именование компонентов

Используется префиксная система:

| Префикс | Назначение |
|---|---|
| `Ui` | базовый UI-компонент |
| `El` | составной универсальный элемент |
| `Pt` | повторяемый паттерн |
| `Tm` | шаблон крупного экрана |
| `use` | composable |
| `format` | чистая функция форматирования |

Примеры:

```text
UiButton
ElInput
PtTable
TmSheetRemoteTransaction
useTableData
formatDateTime
```

Такая система хорошо работает с Nuxt auto-import, поскольку по имени сразу виден уровень компонента.

Конфигурация автоимпорта компонентов находится в `frontend/nuxt.config.js`.

```js
components: [
  {
    path: '~/components/ui',
    prefix: 'Ui',
    pathPrefix: false,
    deep: true,
  },
  {
    path: '~/components/elements',
    pathPrefix: false,
  },
  {
    path: '~/components/patterns',
    pathPrefix: false,
  },
  {
    path: '~/components/templates',
    pathPrefix: false,
  },
]
```

## 4. Таблицы и фильтры

Главный переиспользуемый компонент проекта, это `PtTable`.

Он умеет:

* отображать таблицу;
* строить колонки из конфигурации;
* поддерживать сортировку;
* отображать pagination;
* показывать loading;
* показывать empty state;
* обрабатывать клики по строкам;
* рендерить кастомные ячейки через slots;
* форматировать значения через `filter`;
* встраивать фильтры в header.

Конфигурация колонки выглядит так:

```js
{
  field: 'amount',
  header: 'Сумма',
  align: 'right',
  filter: (value, row) => formatAmount(value, row.currency)
}
```

Поддерживаются следующие поля:

```js
{
  field,
  header,
  sortable,
  slot,
  value,
  filter,
  align
}
```

Кастомные ячейки реализованы через именованные слоты:

```vue
<template #navision_status="{ row }">
  <UiBadge>
    {{ row.navision_status }}
  </UiBadge>
</template>
```

Фильтры описываются декларативно:

```js
{
  key: 'transaction_types',
  type: 'select',
  multiple: true,
  placeholder: 'Тип транзакции',
  options: transactionTypesOptions
}
```

Поддерживаемые типы фильтров:

* `input`;
* `select`;
* `date`;
* `button-group`.

Фильтры синхронизируются с URL. Массивы сериализуются в формат `key[]`.

Основная логика таблиц находится в `useTableData`.

Composable отвечает за:

* загрузку данных;
* текущую страницу;
* сортировку;
* фильтры;
* синхронизацию с query-параметрами;
* пагинацию;
* обновление отдельных строк;
* повторную загрузку после изменения URL.

Это один из самых сильных элементов архитектуры.

## 5. API-слой

API подключается через Nuxt plugin и доступен как `$api`.

Структура API повторяет доменные области:

```js
$api.auth
$api.library
$api.users
$api.remoteUsers
$api.remoteTransactions
$api.stats
$api.excludedUsers
```

Низкоуровневый запрос реализован в `composables/fetch.js`.

Он отвечает за:

* Bearer-токен из cookie;
* разные API URL для SSR и браузера;
* общий `/api` prefix;
* сериализацию массивов;
* глобальные toast-ошибки;
* флаг `silent`.

Страницы не вызывают `$fetch` напрямую. Все прикладные запросы проходят через `$api`. Это правило стоит перенести в новый проект.

## 6. Состояние

Pinia отсутствует.

Используются:

* `ref` и `computed` для локального состояния;
* `useState` для глобального SSR-совместимого состояния;
* `useCookie` для токена и темы;
* `provide/inject` для передачи обновления статуса между страницей и dynamic route.

Основные состояния:

* авторизация: `auth_user`, `auth_loading`;
* справочники: `library_accountants`, `library_transaction_types`;
* тема: `theme`;
* локальные данные таблиц и sheet-компонентов.

Авторизация устроена через `useAuth` и глобальный middleware `auth.global.js`.

Маршруты:

```text
/auth/login
/auth/callback
/access-denied
/remote-users
/remote-users/:id
/remote-transactions
/remote-transactions/:id
/statistics
/excluded-users
/users
```

Dynamic routes используются как URL-состояние открытой панели. Например, список пользователей остаётся под sheet-компонентом, а `/remote-users/123` открывает детали пользователя.

Это удачный UX-подход для административных интерфейсов: состояние панели можно сохранить, открыть в новой вкладке и восстановить кнопкой браузера назад.

## 7. Страницы и пользовательские сценарии

### Авторизация

Страницы:

* `/auth/login`;
* `/auth/callback`;
* `/access-denied`.

Токен приходит через OAuth callback и сохраняется в cookie `auth_token`.

### Пользователи McPay

`/remote-users`:

* таблица пользователей;
* фильтр по имени, email или ID;
* фильтр по типам транзакций;
* сортировка;
* pagination;
* открытие пользователя в sheet.

`/remote-users/:id` открывает `TmSheetRemoteUser`.

### Транзакции

`/remote-transactions`:

* фильтр по бухгалтерии;
* фильтр по типу транзакции;
* фильтр по типу позиции;
* фильтр по датам;
* статусы отправки в Navision;
* переход к деталям транзакции.

`/remote-transactions/:id` открывает `TmSheetRemoteTransaction`.

### Статистика

`/statistics`:

* выбор месяца;
* таблица отправленных данных по компаниям;
* таблица остатков по типам транзакций;
* фильтрация по статусу;
* открытие списка позиций в sheet;
* переход к деталям транзакции.

### Исключённые пользователи

`/excluded-users`:

* добавление пользователя;
* редактирование;
* удаление с подтверждением;
* валидационные ошибки от API;
* обновление таблицы после mutation.

### Администраторы

`/users`:

* список пользователей;
* переключатель admin-прав;
* блокировка изменения собственного статуса;
* локальное обновление изменённой строки.

## 8. Стиль кода

Фактически используется следующий стиль:

* Vue SFC;
* `<script setup>`;
* JavaScript;
* обычно 2 пробела в прикладном коде;
* без обязательных точек с запятой;
* алиасы `@` и `~`;
* явные импорты Vue composables;
* props описываются через объект `defineProps`;
* события описываются через `defineEmits`;
* двусторонняя связь строится через `modelValue`;
* стили почти полностью задаются Tailwind-классами;
* локальный `<style scoped>` используется только для анимации sheet.

При этом style guide технически не закреплён. В `package.json` отсутствуют lint, test, format и typecheck scripts. Есть только:

```text
dev
build
generate
preview
```

Есть смешение стилей:

* прикладные компоненты используют одинарные кавычки и почти не используют `;`;
* сгенерированные `ui`-компоненты используют двойные кавычки и `;`;
* комментарии частично на русском, частично на английском;
* часть компонентов оформлена как shadcn, часть написана вручную.

Для нового проекта стиль нужно закрепить ESLint и Prettier с самого начала.

## 9. Стили и дизайн-система

Стилизация построена на CSS variables и Tailwind.

Основные токены:

```text
--background
--foreground
--card
--popover
--primary
--secondary
--muted
--accent
--destructive
--border
--input
--ring
```

Темная тема активируется классом `.dark`, а переключатель хранит значение в cookie.

Используется семантическая стилизация:

```vue
<div class="bg-background text-foreground border-border">
```

Вместо прямых цветов применяются токены. Это решение нужно сохранить в новом проекте.

Глобальные стили находятся в `assets/css/main.css`, настройки Tailwind в `tailwind.config.js`.

## 10. Что стоит перенести без изменений концепции

Следующие решения выглядят удачно:

* уровни `Ui`, `El`, `Pt`, `Tm`;
* API plugin с доменными namespace;
* единый composable для таблиц;
* декларативные конфигурации колонок;
* декларативные фильтры;
* URL как источник состояния списка;
* slots для специальных ячеек;
* отдельный adapter для Laravel pagination;
* единый форматтер дат, сумм и чисел;
* toast-уведомления через plugin;
* sheet-компоненты для деталей;
* CSS variables для темизации;
* отсутствие прямых API-вызовов из UI-примитивов.

## 11. Что улучшить перед переносом

### 11.1. Добавить feature-слой

Сейчас доменная логика находится в `pages` и `components/templates`.

Для нового проекта лучше добавить:

```text
features/
├── users/
│   ├── api.js
│   ├── composables/
│   ├── components/
│   └── schemas.js
├── transactions/
└── statistics/
```

Слои `Ui`, `El`, `Pt` можно оставить общими. Feature-компоненты лучше изолировать по доменам.

### 11.2. Разделить `PtTable`

Сейчас `PtTable` отвечает сразу за:

* визуализацию;
* pagination;
* sorting;
* filters;
* empty state;
* обработку клика по строке.

Для небольшого проекта это удобно. При росте проекта компонент станет слишком тяжёлым.

Вариант разделения:

```text
DataTable
DataTableHeader
DataTableBody
DataTablePagination
useDataTable
```

### 11.3. Унифицировать загрузку sheet-компонентов

Сейчас таблицы используют `useTableData`, а sheet-компоненты загружают данные вручную через отдельные `ref`, `watch`, `loading` и `catch`.

Логика начинает дублироваться в:

* `TmSheetRemoteUser`;
* `TmSheetRemoteTransaction`;
* `TmSheetStatTransactions`.

В новом проекте стоит выделить composables для detail-загрузки и mutation-сценариев.

### 11.4. Исправить загрузку справочников

`plugins/library.js` загружает справочники глобально. При ошибке он рассчитывает на последующую догрузку, но страницы явно `loadAll()` не вызывают.

Для нового проекта лучше выбрать один вариант:

* загрузка справочников после успешной авторизации;
* composable с гарантированным lazy-load;
* отдельный cached data layer.

### 11.5. Добавить инженерные проверки

Минимальный набор:

```text
eslint
prettier
vitest
vue-tsc или TypeScript
npm run check
npm run test
```

Сейчас автоматических проверок нет.

### 11.6. Проверить дизайн-токены

В Tailwind используются `--radius-lg`, `--radius-md`, `--radius-sm`, но в `main.css` объявлен только `--radius`.

Также используется `--destructive-foreground`, хотя этот токен явно не задан.

В новом проекте токены нужно объявить централизованно и проверить через визуальный smoke test.

### 11.7. Не отключать focus-стили глобально

В `main.css` focus outline и box-shadow отключаются через `!important` для input, select и textarea.

Это ухудшает клавиатурную навигацию. Для нового проекта лучше задавать focus-visible стили на уровне компонентов.

### 11.8. Зафиксировать зависимости

`ElInputCalendar.vue` напрямую импортирует `@internationalized/date`, но пакет явно не указан в `package.json`. Сейчас он приходит транзитивно через другие зависимости.

В новом проекте все пакеты, импортируемые напрямую из исходного кода, нужно указывать в `dependencies` явно.

### 11.9. Учесть безопасность OAuth-токена

Текущая схема получает токен через query-параметр callback и сохраняет его в обычную cookie `auth_token`.

При переносе стоит рассмотреть:

* HttpOnly cookie;
* серверную сессию;
* одноразовый authorization code вместо передачи Bearer-токена через URL;
* очистку query-параметров сразу после callback.

## 12. Рекомендуемая структура нового проекта

```text
frontend/
├── pages/
├── layouts/
├── components/
│   ├── ui/
│   ├── elements/
│   ├── patterns/
│   └── templates/
├── features/
├── composables/
├── services/
├── utils/
├── lib/
├── plugins/
├── assets/
└── tests/
```

## 13. Правило разработки нового экрана

1. Описать API-контракт.
2. Создать feature composable.
3. Описать состояние фильтров и таблицы.
4. Собрать экран из `PtTable`, `PtFilter` и `TmSheet`.
5. Специальные ячейки сделать slots.
6. UI-примитивы не должны знать о домене.
7. Состояние списка синхронизировать с URL.
8. Форматирование вынести в `utils`.
9. Проверить loading, empty, error и mutation-сценарии.
10. Добавить unit-тесты для composables и критичных форматтеров.

## 14. Формула переноса

```text
Ui      отвечает за визуальный примитив
El      добавляет универсальный UX-контракт
Pt      собирает повторяемый паттерн
Tm      реализует крупный экран или доменный сценарий
Page    связывает маршрут, данные и шаблон
Feature изолирует бизнес-домен
```

Такой подход можно использовать как базовый frontend standard для следующего проекта, добавив feature-изоляцию, тесты, строгий style guide и более явный слой API-сервисов.

## 15. Self-check анализа

Проверены:

* структура директорий;
* все страницы;
* composables;
* plugins;
* API-слой;
* UI-примитивы;
* elements;
* patterns;
* templates;
* маршрутизация;
* авторизация;
* состояние;
* таблицы;
* фильтры;
* стили;
* конфигурация Tailwind;
* package scripts;
* наличие тестов и линтеров.

Рабочее дерево исходного проекта Navision осталось чистым. Сборка не запускалась: зависимости в `frontend/node_modules` отсутствуют, а текущий Node.js `12.22.1` ниже требований Nuxt-зависимостей.
