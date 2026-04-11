# Rasti

## Quick Start

```bash
npm install
copy .env.example .env
npm run wp:up
npm run dev
```

## Structure

```text
docker/
  php/
rasti-theme/
.gitignore
.env.example
docker-compose.yml
package.json
README.md
```

## Run Docker

```bash
copy .env.example .env
npm run wp:up
```

WordPress: `http://localhost:8080`  
Adminer: `http://localhost:8081`

## Frontend

Install dependencies:

```bash
npm install
```

Build CSS once:

```bash
npm run build
```

Run watch mode with auto reload:

```bash
npm run dev
```

For auto reload, open the BrowserSync URL shown in the terminal after `npm run dev`.
WordPress runs on `http://localhost:8080`, but live reload works through the BrowserSync local URL.

## Commands

```bash
npm run wp:up
npm run wp:down
npm run wp:logs
npm run build
npm run dev
```

## Deploy Path

```text
rasti-theme
```
