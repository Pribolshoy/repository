.PHONY: help install test test-coverage test-coverage-docker test-docker clean clean-docker docker-build lint psalm phpstan phpmd phan noverify rector deptrac deptrac-graph-image check-all

# Переменные
PHP := php
COMPOSER := composer
VENDOR_BIN := vendor/bin

export args=$0

args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`

help: ## Показать справку по командам
	@echo "Доступные команды:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-20s %s\n", $$1, $$2}'

install: ## Установить зависимости через Composer
	@echo "Установка зависимостей..."
	$(COMPOSER) install

update: ## Обновить зависимости через Composer
	@echo "Обновление зависимостей..."
	$(COMPOSER) update

test: ## Запустить тесты
	@echo "Запуск тестов..."
	$(VENDOR_BIN)/phpunit $(call args,)

test-coverage: ## Запустить тесты с покрытием кода
	@echo "Запуск тестов с покрытием кода..."
	$(VENDOR_BIN)/phpunit --testdox --coverage-html coverage

test-coverage-docker: ## Запустить тесты с покрытием через Docker Compose
	@echo "Запуск тестов с покрытием кода через Docker Compose..."
	@docker-compose run --rm test

test-docker: ## Запустить тесты через Docker Compose (без покрытия)
	@echo "Запуск тестов через Docker Compose..."
	@docker-compose run --rm test sh -c "if [ ! -d vendor ]; then composer install --no-interaction --prefer-dist; fi && vendor/bin/phpunit --testdox"

psalm: ## Запустить статический анализ через Psalm
	@echo "Запуск Psalm..."
	$(VENDOR_BIN)/psalm

phpstan: ## Запустить статический анализ через PHPStan
	@echo "Запуск PHPStan..."
	$(VENDOR_BIN)/phpstan analyse $(call args,)

phpmd: ## Запустить анализ кода через PHPMD
	@echo "Запуск PHPMD..."
	$(VENDOR_BIN)/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode

phan: ## Запустить статический анализ через Phan
	@echo "Запуск Phan..."
	$(VENDOR_BIN)/phan $(call args,)

noverify: ## Запустить статический анализ через NoVerify
	@echo "Запуск NoVerify..."
	$(VENDOR_BIN)/noverify check ./src

rector: ## Запустить рефакторинг через Rector
	@echo "Запуск Rector..."
	$(VENDOR_BIN)/rector process src --dry-run

rector-fix: ## Применить рефакторинг через Rector
	@echo "Применение рефакторинга Rector..."
	$(VENDOR_BIN)/rector process src

deptrac: ## Запустить анализ зависимостей через Deptrac
	@echo "Запуск Deptrac..."
	$(VENDOR_BIN)/deptrac analyse $(call args,)

deptrac-graph-image: ## Создать PNG изображение графа зависимостей (требует GraphViz)
	@mkdir -p docs/deptrac
	@echo "Генерация DOT файла и конвертация в PNG изображение..."
	@if command -v dot >/dev/null 2>&1; then \
		$(VENDOR_BIN)/deptrac analyse --formatter=graphviz-dot --output=docs/deptrac/graph.dot && \
		dot -Tpng docs/deptrac/graph.dot -o docs/deptrac/graph.png && \
		rm -f docs/deptrac/graph.dot && \
		echo "Изображение сохранено в docs/deptrac/graph.png"; \
	else \
		echo "Ошибка: GraphViz не установлен. Установите его командой:"; \
		echo "  sudo apt-get install graphviz  # для Ubuntu/Debian"; \
		echo "  sudo yum install graphviz     # для CentOS/RHEL"; \
		echo "  brew install graphviz         # для macOS"; \
		exit 1; \
	fi

lint: psalm phpstan phpmd ## Запустить все инструменты линтинга

check-all: test lint deptrac ## Запустить все проверки (тесты, линтинг, анализ зависимостей)

clean: ## Очистить временные файлы и кэш
	@echo "Очистка временных файлов..."
	rm -rf coverage/
	rm -rf .phpunit.cache/
	rm -rf vendor/
	rm -rf docs/deptrac/*.png 2>/dev/null || true
	find . -type d -name ".phpunit.cache" -exec rm -rf {} + 2>/dev/null || true
	find . -type f -name "*.cache" -delete 2>/dev/null || true

clean-cache: ## Очистить только кэш PHPUnit
	@echo "Очистка кэша PHPUnit..."
	rm -rf .phpunit.cache/

clean-docker: ## Остановить и удалить контейнеры Docker
	@echo "Остановка и удаление контейнеров Docker..."
	@docker-compose down -v 2>/dev/null || true
	@echo "Контейнеры остановлены и удалены"

docker-build: ## Собрать Docker образ для тестов
	@echo "Сборка Docker образа для тестов..."
	@docker-compose build test

autoload: ## Обновить автозагрузку Composer
	@echo "Обновление автозагрузки..."
	$(COMPOSER) dump-autoload

