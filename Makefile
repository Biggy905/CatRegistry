# ─────────────────────────────────────────────────────────────
# Cat Registry — Makefile
# ─────────────────────────────────────────────────────────────
.DEFAULT_GOAL := help
SHELL := /bin/bash

COMPOSE      := docker compose
NETWORK_NAME := cat-registry-network

# Имена сервисов из docker-compose.yaml
NGINX   := cat-registry-nginx
PHP     := cat-registry-php-fpm
FRONT   := cat-registry-frontend
PG      := cat-registry-postgres

# Рабочая директория backend внутри контейнера
BACKEND_DIR := /app/backend

# Флаги для консольных команд Yii2 (без интерактива)
YII_FLAGS := --interactive=0

# ─────────────────────────────────────────────────────────────
# Первый запуск
# ─────────────────────────────────────────────────────────────
.PHONY: init
init: env network ## Первый запуск: .env, сеть, сборка, подъём, зависимости, миграции
	@echo "→ Сборка и подъём контейнеров..."
	$(COMPOSE) up -d --build

	@echo "→ Ожидание готовности PostgreSQL..."
	@until $(COMPOSE) exec -T $(PG) pg_isready -U postgres -d db >/dev/null 2>&1; do \
		printf "."; \
		sleep 1; \
	done; \
	echo " ok"

	@echo "→ Установка PHP-зависимостей..."
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) composer install

	@echo "→ Применение миграций..."
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) php console migrate $(YII_FLAGS)

	@echo "→ Ожидание готовности frontend (npm install)..."
	@until $(COMPOSE) exec -T $(FRONT) test -d node_modules >/dev/null 2>&1; do \
		printf "."; \
		sleep 2; \
	done; \
	echo " ok"

	@echo ""
	@echo "✓ Готово."
	@echo "  Backend:  http://localhost:7000"
	@echo "  Frontend: http://localhost:8088"

# ─────────────────────────────────────────────────────────────
# Окружение и сеть
# ─────────────────────────────────────────────────────────────
.PHONY: env
env: ## Создать .env из .env.example (если .env нет)
	@if [ ! -f .env ] && [ -f .env.example ]; then \
		cp .env.example .env; \
		echo "→ Создан .env из .env.example"; \
	elif [ -f .env ]; then \
		echo "→ .env уже существует, пропускаем"; \
	else \
		echo "⚠ .env.example не найден — создайте .env вручную"; \
	fi

.PHONY: network
network: ## Создать Docker-сеть, если её нет
	@if ! docker network inspect $(NETWORK_NAME) >/dev/null 2>&1; then \
		docker network create $(NETWORK_NAME); \
		echo "→ Создана сеть $(NETWORK_NAME)"; \
	else \
		echo "→ Сеть $(NETWORK_NAME) уже существует"; \
	fi

# ─────────────────────────────────────────────────────────────
# Обычные команды
# ─────────────────────────────────────────────────────────────
.PHONY: up
up: network ## Поднять контейнеры (без сборки)
	$(COMPOSE) up -d

.PHONY: down
down: ## Остановить контейнеры
	$(COMPOSE) down

.PHONY: restart
restart: ## Перезапустить контейнеры
	$(COMPOSE) restart

.PHONY: logs
logs: ## Логи всех контейнеров
	$(COMPOSE) logs -f

.PHONY: logs-php
logs-php: ## Логи php-fpm
	$(COMPOSE) logs -f $(PHP)

.PHONY: logs-front
logs-front: ## Логи frontend
	$(COMPOSE) logs -f $(FRONT)

.PHONY: logs-nginx
logs-nginx: ## Логи nginx
	$(COMPOSE) logs -f $(NGINX)

.PHONY: ps
ps: ## Статус контейнеров
	$(COMPOSE) ps

# ─────────────────────────────────────────────────────────────
# Backend
# ─────────────────────────────────────────────────────────────
.PHONY: migrate
migrate: ## Применить миграции (без подтверждения)
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) php console migrate $(YII_FLAGS)

.PHONY: migrate-down
migrate-down: ## Откатить последнюю миграцию (без подтверждения)
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) php console migrate/down 1 $(YII_FLAGS)

.PHONY: migrate-fresh
migrate-fresh: ## ⚠ Drop всех таблиц и заново применить миграции
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) php console migrate/fresh $(YII_FLAGS)

.PHONY: migrate-create
migrate-create: ## Создать миграцию: make migrate-create name=create_foo_table
	@if [ -z "$(name)" ]; then echo "Укажите name=..."; exit 1; fi
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) php console migrate/create $(name) \
		--namespace='CatRegistry\applications\migrations'

.PHONY: composer
composer: ## Установить composer-зависимости
	$(COMPOSE) exec -T -w $(BACKEND_DIR) $(PHP) composer install

.PHONY: bash-php
bash-php: ## Зайти в PHP-контейнер
	$(COMPOSE) exec $(PHP) bash

# ─────────────────────────────────────────────────────────────
# Frontend
# ─────────────────────────────────────────────────────────────
.PHONY: npm-install
npm-install: ## Установить npm-зависимости
	$(COMPOSE) exec -T $(FRONT) npm install

.PHONY: npm-build
npm-build: ## Собрать фронт для продакшена
	$(COMPOSE) exec -T $(FRONT) npm run build

.PHONY: bash-front
bash-front: ## Зайти в frontend-контейнер
	$(COMPOSE) exec $(FRONT) sh

# ─────────────────────────────────────────────────────────────
# Очистка
# ─────────────────────────────────────────────────────────────
.PHONY: clean
clean: ## Остановить и удалить контейнеры + volumes (БД удалится!)
	$(COMPOSE) down -v

.PHONY: clean-all
clean-all: clean ## + удалить Docker-сеть
	@if docker network inspect $(NETWORK_NAME) >/dev/null 2>&1; then \
		docker network rm $(NETWORK_NAME); \
		echo "→ Сеть $(NETWORK_NAME) удалена"; \
	fi

# ─────────────────────────────────────────────────────────────
# Справка
# ─────────────────────────────────────────────────────────────
.PHONY: help
help: ## Показать список команд
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'
