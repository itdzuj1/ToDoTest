# ToDoTest — контекст нового проекта

## Цель

Создать небольшой учебный full-stack todo-проект с более содержательной логикой, чем базовый CRUD.
Это отдельный проект, не связанный с предметной областью 7588.

## Состав

- Один Nuxt 3 frontend для пользовательской и административной зон.
- Свежий Laravel backend.
- PostgreSQL.
- Docker Compose для локальной разработки.
- Мобильных приложений нет.

Административная зона должна находиться внутри того же Nuxt-приложения (`/admin`) и использовать
отдельный layout и route middleware. Не разделять frontend на два приложения.

## Откуда взяты подходы

За основу взят инженерный стиль проекта `/Users/vlad/work/7588`, но без копирования его legacy-кода.

Сохраняемые идеи:

- понятная иерархия UI-компонентов;
- централизованный API-клиент;
- Form Requests и API Resources;
- тонкие контроллеры;
- Actions/Services только для нетривиальной логики;
- единый контракт полевых ошибок;
- migrations, factories и seeders;
- явные роли и права, если они потребуются;
- Docker и короткие команды для разработки.

Не переносить:

- Nuxt 2, Vuex, mixins и глобальные event bus;
- два отдельных Nuxt-приложения;
- fat models и тяжёлые observers;
- глобальные helpers для бизнес-логики;
- SQL-интерполяцию и `request()` внутри моделей;
- скрытые ошибки и пустые `catch`;
- устаревшие Docker-образы и `chmod 777`;
- искусственное enterprise-усложнение маленького проекта.

## Предпочтительный стек

При создании проекта проверить актуальные стабильные версии:

- Nuxt 3, Vue 3, TypeScript strict, Composition API;
- Pinia только для действительно общего состояния;
- `$fetch`/`ofetch` и типизированный API-слой;
- SCSS и CSS custom properties для design tokens;
- Laravel на актуальной стабильной версии, PHP 8.3+;
- Laravel Pint, PHPStan, Pest;
- ESLint, Prettier, Vitest и Playwright;
- PostgreSQL;
- Docker Compose.

Не добавлять Redis, queues, websockets и другие сервисы до появления реальной задачи.

## Структура репозитория

```text
ToDoTest/
├── backend/
├── frontend/
├── docker/
├── docs/
├── .cursor/rules/
├── compose.yml
├── Makefile
└── README.md
```

## Стиль frontend

- Vue SFC: `<template>` → `<script setup lang="ts">` → `<style scoped lang="scss">`.
- В script: imports → props/emits → composables/stores → state → computed → watchers/lifecycle → handlers.
- Универсальные элементы именовать `UiButton`, `UiInput`, `UiModal`.
- Feature-компоненты именовать по назначению: `TodoCard`, `TodoForm`, `AdminUserTable`.
- Компоненты в шаблонах писать в kebab-case.
- Повторяемую логику выносить в composables, но не создавать composable для однострочной операции.
- UI-компоненты не должны вручную собирать API URL.
- Локальное состояние оставлять локальным; Pinia не использовать как свалку.
- Ошибки API не скрывать; 422 отображать у соответствующих полей.

## Design tokens

Все визуальные значения централизуются:

```text
frontend/assets/styles/
├── tokens/
│   ├── _colors.scss
│   ├── _spacing.scss
│   ├── _typography.scss
│   ├── _sizes.scss
│   ├── _radii.scss
│   ├── _shadows.scss
│   ├── _breakpoints.scss
│   └── _transitions.scss
├── themes/
│   ├── default.scss
│   └── dark.scss
├── _mixins.scss
├── _reset.scss
└── main.scss
```

Компоненты используют семантические переменные (`--color-primary`, `--color-surface`,
`--color-text`, `--color-border`), а не hex-значения. Смена палитры должна выполняться в theme-файле.

## Стиль backend

Базовый поток:

```text
Route → FormRequest → Controller → optional Action/Service → Model → Resource
```

- Классы application-слоя по возможности `final`.
- Constructor property promotion и `readonly` для зависимостей.
- Явные типы аргументов и возвращаемых значений.
- Controller отвечает только за authorization, orchestration и response.
- FormRequest отвечает за authorization, normalization и validation.
- Resource определяет публичный JSON-контракт.
- Service/Action создаётся только для законченного use case или нетривиальной переиспользуемой логики.
- Многошаговые изменения выполнять через `DB::transaction()`.
- Использовать Policies, а не глобальные permission helpers.
- Модели не должны зависеть от HTTP request.
- Запросы строить через Query Builder и parameter binding.
- Создание/изменение возвращает актуальный Resource; удаление — явный 204.

## API

- URL — kebab-case.
- JSON — snake_case.
- TypeScript-методы и локальные переменные — camelCase.
- `POST` для создания, `PATCH` для изменения, `DELETE` для удаления/архивации по выбранному контракту.
- Validation response: HTTP 422 и `{ errors: { field: string[] } }`.
- Не дублировать endpoint paths по компонентам.
- При стабилизации API добавить OpenAPI и генерацию типов либо единый ручной typed client.

## Форматирование

- UTF-8, LF, final newline.
- 4 пробела; YAML — 2.
- TypeScript: single quotes, без semicolon, trailing comma в multiline, print width 120.
- PHP: Laravel Pint.
- Комментарии объясняют причину или неочевидное правило, а не пересказывают код.
- Закомментированный код удалять: история хранится в Git.

## Качество

- Не подавлять ошибки без причины.
- Не использовать `any` без локального обоснования.
- Проверять edge cases и входные данные.
- Feature-тесты backend: auth, permissions и todo flows.
- Frontend: unit-тесты самостоятельной логики и Playwright для главного пользовательского flow.
- Lint, static analysis, tests и builds должны выполняться в CI.

## Инструкция новому агенту

1. Прочитать этот файл и `.cursor/rules/*.mdc`.
2. Не начинать реализацию до уточнения функциональных требований todo-приложения.
3. Предложить минимальную архитектуру, пропорциональную размеру проекта.
4. Перед установкой зависимостей проверить актуальные официальные версии.
5. Сначала создать foundation: проекты, Docker, lint/format/static analysis и базовые tokens.
6. Затем реализовывать функциональность вертикальными небольшими срезами.
