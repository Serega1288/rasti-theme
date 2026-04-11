# Rasti

## Швидкий старт

```bash
npm install
npm run wp:up
npm run dev
```

Налаштування автoвстановлення плагінів у `.env`, наприклад:

```env
WP_AUTO_PLUGINS=woocommerce,advanced-custom-fields
```

## Структура

```text
docker/
  php/
rasti-theme/
plugins-archives/
.gitignore
.env.example
docker-compose.yml
package.json
README.md
```

## Запуск Docker

```bash
npm run wp:up
```

Плагіни з `WP_AUTO_PLUGINS` встановлюються автоматично після встановлення WordPress у Docker.  
Кастомні плагіни з `plugins-archives/` також встановлюються автоматично з папок або zip-архівів.

WordPress: `http://localhost:8080`  
Adminer: `http://localhost:8081`

## Frontend

Встановити залежності:

```bash
npm install
```

Разова збірка CSS:

```bash
npm run build
```

Запуск watch-режиму з автооновленням:

```bash
npm run dev
```

Для автооновлення відкривай URL BrowserSync, який з’явиться в терміналі після `npm run dev`.  
`http://localhost:8080` — це сам WordPress, а live reload працює через BrowserSync URL.

## Команди

```bash
npm run wp:up
npm run wp:down
npm run wp:logs
npm run db:export
npm run db:import
npm run build
npm run dev
```

## База даних

Експорт бази:

```bash
npm run db:export
```

Імпорт бази:

```bash
npm run db:import
```

Шлях до дампа:

```text
database/wordpress.sql
```

## Шлях для деплою

```text
rasti-theme
```
