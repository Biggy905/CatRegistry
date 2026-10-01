# Cat Registry

Реестр кошек: REST API на Yii2 + SPA на Vue 3.

## Стек

- **Backend:** Yii2, PHP 8.4, PostgreSQL 18, nginx, PHP-FPM
- **Frontend:** Vue 3, TypeScript, Pinia, Vue Router, Bootstrap 5, Vite
- **Инфраструктура:** Docker Compose

## Требования

- Docker ≥ 24
- Docker Compose ≥ 2.20
- GNU Make
- Свободные порты: `7000`, `8088`, `5432`

## Первый запуск

```bash
make init
```

Что произойдёт:
1. Соберётся образ PHP-FPM.
2. Поднимутся контейнеры: nginx, php-fpm, frontend, postgres.
3. Установятся composer- и npm-зависимости.
4. Применятся миграции БД.

После успешного запуска:

- **Frontend:** http://localhost:8088
- **Backend API:** http://localhost:7000

## Обычный запуск

```bash
make up
```

Если нужно остановить:

```bash
make down
```

## Полезные команды

```bash
make help           # список всех команд
make logs           # логи всех контейнеров
make ps             # статус контейнеров

make migrate        # применить миграции
make migrate-down   # откатить последнюю
make migrate-create name=create_foo_table

make composer       # composer install
make npm-install    # npm install
make npm-build      # сборка фронта для продакшена

make bash-php       # зайти в PHP-контейнер
make bash-front     # зайти в frontend-контейнер
```

## Полная очистка

```bash
make clean
```

⚠️ **Внимание:** `make clean` удаляет volumes, в том числе **базу данных**. Все данные будут потеряны.

## Структура проекта

```
.
├── backend/               # Yii2 (API)
│   ├── src/
│   │   ├── components/  # компоненты
│   │   ├── controllers/  # контроллеры
│   │   ├── entities/  # сущности
│   │   ├── queries/  # запросы
│   │   ├── repositories/  # репозитории
│   │   ├── forms/  # формы
│   │   ├── exceptions/  # исключения
│   │   ├── groups/  # группы, value->object
│   │   ├── enums/        # перечисления
│   │   ├── migrations/        # миграции
│   │   ├── services/        # сервисы
│   │   ├── runtime/        # среда выполнения
│   │   ├── config/        # конфиги приложения
│   │   └── public/        # точка входа (index.php)
│   └── console            # консольная точка входа
├── frontend/              # Vue 3 SPA
│   ├── src/
│   │   ├── api/           # axios-клиент и методы API
│   │   ├── components/    # Vue-компоненты
│   │   ├── stores/        # Pinia
│   │   ├── types/         # TS-типы
│   │   └── views/         # страницы
│   └── vite.config.ts
├── docker/
│   ├── nginx/docker.conf
│   └── php/PHP_FPM
├── docker-compose.yaml
├── Makefile
└── README.md
```

## Переменные окружения

Backend читает `.env` в корне проекта (см. `.env.example`). Основное:

| Переменная | Назначение |
|---|---|
| `DB_HOST` | Хост PostgreSQL (`cat-registry-postgres`) |
| `DB_PORT` | Порт (`5432`) |
| `DB_NAME` | Имя БД (`db`) |
| `DB_USER` | Пользователь (`postgres`) |
| `DB_PASSWORD` | Пароль (`postgres`) |

## Частые проблемы

### Порт уже занят

```
Error: bind: address already in use
```

Проверь, что порты 7000, 8088, 5432 свободны:

```bash
lsof -i :7000
lsof -i :8088
lsof -i :5432
```

Или поменяй проброс портов в `docker-compose.yaml`.

### `php console migrate` падает с `connection refused`

Postgres не успел стартовать. Подожди 5–10 секунд и повтори:

```bash
make migrate
```

Если повторяется — проверь `healthcheck` в `docker-compose.yaml`.

### Фронт не видит API

Проверь `frontend/vite.config.ts` → `server.proxy['/api'].target`. Должно быть `http://cat-registry-nginx:7000` (внутри Docker-сети).

### Ошибка `Invalid path alias: @CatRegistry`

Алиасы не загрузились. Проверь `backend/src/config/aliases.php` и порядок `require` в `backend/console` / `backend/public/index.php`.

## Разработка

- **Фронт** — Vite dev-сервер на 8088 с HMR. Изменения в `.vue`/`.ts` подхватываются автоматически.
- **Бэк** — PHP-FPM. Изменения в `.php` подхватываются сразу, перезапуск не нужен.
- **Миграции** — после `git pull` не забудь `make migrate`.
- **Новые зависимости** — после `git pull`:
  ```bash
  make composer
  make npm-install
  ```