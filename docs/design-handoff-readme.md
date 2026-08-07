# Handoff: ToDoTest — дизайн-система и экраны

## Overview
Пакет для реализации фронтенда ToDoTest (Nuxt 3 + Vue 3 + TS strict + SCSS): менеджер todo-списков с публичной зоной (демо-профили, списки, задачи) и админ-зоной (`/admin`: dashboard, пользователи, задачи, блокировки, удаление с подтверждением).

## О файлах в пакете
Файлы в `reference/` — **дизайн-референс, сделанный в HTML**. Это прототип, показывающий внешний вид, плотность и состояния, **а не продакшн-код для копирования**. Задача — воссоздать эти экраны в целевом стеке (Nuxt 3 / Vue 3 / SCSS) по паттернам, описанным в `docs/`, используя готовые токены из `tokens/`.

## Fidelity
**High-fidelity.** Цвета, типографика, размеры контролов, радиусы и состояния — финальные. Воспроизводить попиксельно, но средствами Vue-компонентов и SCSS, а не копированием инлайн-стилей из HTML.

## Как использовать с Cursor
1. Скопируйте `cursor-rules/` в корень репозитория и переименуйте в `.cursor/rules/` (файлы `.mdc` подхватываются автоматически).
2. Скопируйте `tokens/` в `frontend/assets/styles/tokens/` и `themes/` в `frontend/assets/styles/themes/`.
3. Откройте `docs/reference/todotest-design-reference.html` в браузере, это визуальный эталон.
4. Стартовый промпт агенту:
   > Реализуй экран `pages/lists/[id].vue` по `docs/screens.md` (раздел «Todo list details») и контрактам из `docs/components.md`. Соблюдай правила из `.cursor/rules`. Визуальный эталон — `docs/reference/todotest-design-reference.html`.

## Состав пакета
```
docs/
  concept.md        — визуальная концепция и принципы
  screens.md        — все экраны: layout, компоненты, копия, размеры
  components.md     — контракты Ui*/El*/Pt*/Tm*: props, emits, slots
  states.md         — матрица состояний и доменные правила
  accessibility.md  — требования WCAG AA и клавиатура
  tests.md          — unit / component / e2e сценарии
tokens/             — готовые SCSS-файлы токенов
themes/             — default.scss, dark.scss
cursor-rules/       — правила для Cursor (.mdc)
reference/          — HTML-эталон дизайна
```

## Стек и запреты
Nuxt 3, Vue 3, TypeScript strict, `<script setup lang="ts">`, SCSS + CSS custom properties, typed API layer, Vitest, Playwright, ESLint, Prettier.

Запрещено: Tailwind, JS-only компоненты, глобальный event bus, хардкод URL эндпоинтов в компонентах, raw hex внутри компонентов, статические inline-стили, «тихие» ошибки.
