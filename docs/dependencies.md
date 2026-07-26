# Версии зависимостей

Проверено 26 июля 2026 года по официальной документации и реестрам пакетов.

- Node.js 24.18 LTS
- Nuxt 3.21.9
- Vue 3.5.40
- Laravel Framework 13.22
- PHP 8.4
- PostgreSQL 18.4
- Pest 4.7
- PHPStan 2.2 с Larastan 3.10
- Vitest 4.1
- Playwright 1.62

## Известное предупреждение npm audit

В Nuxt 3.21.9/Nitro 2.13.4 остаётся транзитивная уязвимость `brace-expansion`
([GHSA-mh99-v99m-4gvg](https://github.com/advisories/GHSA-mh99-v99m-4gvg)).
Она приходит через build/archive-инструменты Nitro; приложение не передаёт в них пользовательские шаблоны.

`npm audit fix --force` предлагает переход на другую major-версию Nuxt, поэтому автоматическое
breaking-обновление не применялось. Предупреждение нужно повторно проверить при каждом обновлении Nuxt 3
или при согласованном переходе на Nuxt 4.

