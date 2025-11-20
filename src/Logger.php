<?php

namespace pribolshoy\repository;

/**
 * Класс для сбора логов операций Redis.
 * Собирает информацию о выполненных операциях: действие, ключ и количество полученных данных.
 *
 * @package pribolshoy\repository
 */
class Logger
{
    /**
     * Доступные категории логов.
     *
     * @var array
     */
    protected static array $categories = [
        'cache',
        'repository',
        'service',
        'default',
    ];

    /**
     * Включено ли логирование.
     *
     * @var bool
     */
    protected static bool $enabled = true;

    /**
     * Собранные логи.
     *
     * @var array
     */
    protected static array $logs = [];

    /**
     * Максимальное количество логов для хранения в памяти.
     *
     * @var int
     */
    protected static int $maxLogs = 1000;

    /**
     * Кэш активных категорий из переменной окружения.
     *
     * @var array|null
     */
    protected static ?array $activeCategories = null;

    /**
     * Включить логирование.
     *
     * @return void
     */
    public static function enable(): void
    {
        self::$enabled = true;
    }

    /**
     * Выключить логирование.
     *
     * @return void
     */
    public static function disable(): void
    {
        self::$enabled = false;
    }

    /**
     * Проверить, включено ли логирование.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    /**
     * Установить максимальное количество логов.
     *
     * @param int $maxLogs
     *
     * @return void
     */
    public static function setMaxLogs(int $maxLogs): void
    {
        self::$maxLogs = $maxLogs;
    }

    /**
     * Получить список доступных категорий.
     *
     * @return array
     */
    public static function getCategories(): array
    {
        return self::$categories;
    }

    /**
     * Добавить категорию в список доступных.
     *
     * @param string $category
     *
     * @return void
     */
    public static function addCategory(string $category): void
    {
        if (!in_array($category, self::$categories, true)) {
            self::$categories[] = $category;
        }
    }

    /**
     * Проверить, является ли категория допустимой.
     *
     * @param string $category
     *
     * @return bool
     */
    protected static function isValidCategory(string $category): bool
    {
        return in_array($category, self::$categories, true);
    }

    /**
     * Получить список активных категорий из переменной окружения.
     * Если переменная не установлена или пуста, возвращает null (все категории активны).
     *
     * @return array|null
     */
    protected static function getActiveCategories(): ?array
    {
        if (self::$activeCategories === null) {
            $envValue = getenv('REPOSITORY_LOG_CATEGORIES');
            
            if ($envValue === false || trim($envValue) === '') {
                // По умолчанию все категории активны
                self::$activeCategories = null;
            } else {
                // Парсим список категорий (разделитель - запятая)
                $categories = array_map('trim', explode(',', $envValue));
                $categories = array_filter($categories, function ($cat) {
                    return !empty($cat);
                });
                self::$activeCategories = array_values($categories);
            }
        }

        return self::$activeCategories;
    }

    /**
     * Проверить, разрешена ли категория для логирования.
     * Если список активных категорий не задан (null), разрешены все категории.
     *
     * @param string $category
     *
     * @return bool
     */
    protected static function isCategoryAllowed(string $category): bool
    {
        $activeCategories = self::getActiveCategories();
        
        // Если список не задан, разрешены все категории
        if ($activeCategories === null) {
            return true;
        }

        // Проверяем, есть ли категория в списке активных
        return in_array($category, $activeCategories, true);
    }

    /**
     * Добавить лог операции Redis.
     *
     * @param string $operation Название операции (get, set, delete и т.д.)
     * @param string $key Ключ Redis
     * @param string $category Категория лога
     * @param mixed $result Результат операции (для операций чтения)
     *
     * @return void
     */
    public static function log(
        string $operation,
        string $key,
        string $category = 'default',
        $result = null
    ): void {
        if (!self::$enabled) {
            return;
        }

        // Проверяем валидность категории, если невалидна - используем default
        if (!self::isValidCategory($category)) {
            $category = 'default';
        }

        // Проверяем, разрешена ли категория для логирования
        if (!self::isCategoryAllowed($category)) {
            return;
        }

        $log = [
            'operation' => $operation,
            'key' => $key,
            'category' => $category,
        ];

        // Для операций чтения добавляем количество полученных данных
        if ($result !== null) {
            $log['count'] = self::getCount($result);
        }

        self::addLog($log);
    }

    /**
     * Получить количество элементов в результате.
     *
     * @param mixed $result
     *
     * @return int
     */
    protected static function getCount($result): int
    {
        if (is_array($result)) {
            return count($result);
        }

        if (is_object($result)) {
            return 1;
        }

        if ($result !== null && $result !== false && $result !== '') {
            return 1;
        }

        return 0;
    }

    /**
     * Добавить лог в массив.
     *
     * @param array $log
     *
     * @return void
     */
    protected static function addLog(array $log): void
    {
        self::$logs[] = $log;

        // Ограничиваем количество логов в памяти
        if (count(self::$logs) > self::$maxLogs) {
            array_shift(self::$logs);
        }
    }

    /**
     * Получить все логи.
     *
     * @return array
     */
    public static function getLogs(): array
    {
        return self::$logs;
    }

    /**
     * Получить логи по операции.
     *
     * @param string $operation
     *
     * @return array
     */
    public static function getLogsByOperation(string $operation): array
    {
        return array_filter(self::$logs, function ($log) use ($operation) {
            return isset($log['operation']) && $log['operation'] === $operation;
        });
    }

    /**
     * Получить логи по категории.
     *
     * @param string $category
     *
     * @return array
     */
    public static function getLogsByCategory(string $category): array
    {
        return array_filter(self::$logs, function ($log) use ($category) {
            return isset($log['category']) && $log['category'] === $category;
        });
    }

    /**
     * Очистить все логи.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$logs = [];
    }

    /**
     * Получить количество логов.
     *
     * @return int
     */
    public static function count(): int
    {
        return count(self::$logs);
    }
}

