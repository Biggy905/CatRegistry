# ─────────────────────────────────────────────────────────────
# Cat Registry — Makefile
# ─────────────────────────────────────────────────────────────
.DEFAULT_GOAL := help
SHELL := /bin/bash

COMPOSE      := docker compose
NETWORK_NAME := cat-registry-network

# Имена сервисов из docker-compose.yaml
PHP     := cat-registry-php-fpm
FRONT   := cat-registry-frontend

# ─────────────────────────────────────────────────────────────
# Первый запуск
# ─────────────────────────────────────────────────────────────
.PHONY: init
init: env network ## Первый запуск: .env, сеть, сборка, подъём, зависимости, миграции
	@echo "→ Сборка и подъём контейнеров..."
	$(COMPOSE) up -d --build

	@echo "→ Ожидание готовности PostgreSQL..."
	@until $(COMPOSE) exec -T cat-registry-postgres pg_isready -U postgres -d db >/dev/null 2>&1; do \
		printf "."; \
		sleep 1; \
	done; \
	echo " ok"

	@echo "→ Установка PHP-зависимостей..."
	$(COMPOSE) exec -T $(PHP) composer install

	@echo "→ Установка npm-зависимостей..."
	$(COMPOSE) exec -T $(FRONT) npm install

	@echo "→ Применение миграций..."
	$(COMPOSE) exec -T $(PHP) php console migrate

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

.PHONY: ps
ps: ## Статус контейнеров
	$(COMPOSE) ps

# ─────────────────────────────────────────────────────────────
# Backend
# ─────────────────────────────────────────────────────────────
.PHONY: migrate
migrate: ## Применить миграции
	$(COMPOSE) exec -T $(PHP) php console migrate

.PHONY: migrate-down
migrate-down: ## Откатить последнюю миграцию
	$(COMPOSE) exec -T $(PHP) php console migrate/down 1

.PHONY: migrate-create
migrate-create: ## Создать миграцию: make migrate-create name=create_foo_table
	@if [ -z "$(name)" ]; then echo "Укажите name=..."; exit 1; fi
	$(COMPOSE) exec -T $(PHP) php console migrate/create $(name) \
		--namespace='CatRegistry\applications\migrations'

.PHONY: composer
composer: ## Установить composer-зависимости
	$(COMPOSE) exec -T $(PHP) composer install

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
