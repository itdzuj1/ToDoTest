---
paths:
  - "frontend/**/*.{vue,scss}"
---

# Дизайн-токены и стили

Все значения — из `frontend/assets/styles/tokens/` через `var(--*)`. Компоненты используют семантические переменные (`--color-primary`, `--color-surface`, `--color-surface-muted`, `--color-text`, `--color-text-muted`, `--color-border`, `--color-danger`, `--color-focus`), а не примитивную палитру. Raw hex, сырые px для spacing/radius, имена шрифтов и статические inline-стили внутри компонентов запрещены; inline допустим только для действительно динамических значений.

## Палитра (для ориентира — в коде только переменные)

- primary `#202020` / hover `#2a2a2a` — основные CTA и заголовки;
- brand `#6647f0` — идентичность, выбранные состояния, brand-CTA;
- link/focus `#0091ff` — ссылки, focus-ring, вторичные действия;
- success `#00c07a`, warning `#f59e0b`, danger `#d92d20`;
- border `#e8e8e8`, border-strong `#d4d4d4`, surface-muted `#f8f9fa`, surface-subtle `#e9ebf0`;
- чистый `#000` не используется ни для текста, ни для фонов;
- текст на tint-фонах — `*-strong` шаги (`#046c4a`, `#0060a9`, `#96620a`, `#a4231a`).

## Типографика

`--font-display` Plus Jakarta Sans (заголовки), `--font-body` Inter (текст), `--font-mono` JetBrains Mono (ID, даты, meta).

Шкала: 14 / 16 / 20 / 34 / 48 / 60 / 80. Рабочие экраны — 34–48; 60–80 только profile selection и admin login. Заголовки: weight 650–800, letter-spacing ≈ −0.04em, выравнивание по левому краю.

## Геометрия

Радиусы: input 9px · card 12px · image 16px · large card 20px · buttons/tabs/badges/tags/chips `9999px`.
Spacing кратен 4: element gap 12, card padding 28, section gap 80, max-width контента 1200.

## SCSS

- Scoped SCSS, BEM для нетривиальных компонентов: `.pt-table__row--selected`.
- Состояния — модификаторы классов (`--loading`, `--readonly`, `--invalid`), не inline-стили.
- Интерактивные состояния обязательны: hover, active, focus-visible, disabled, invalid, loading.
- Глобальные стили — только reset, typography defaults, tokens и utilities.
- Одна система токенов для публичной и админской зон; тема меняется только переопределением переменных в `assets/styles/themes/`.
- Новый токен — только если это переиспользуемое дизайн-решение; primitive palette отделена от семантических токенов.
