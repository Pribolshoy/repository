<?php

namespace pribolshoy\repository;

/**
 * Класс конфигурации для кеширования.
 * Содержит единые настройки делимитеров для ключей кеша.
 *
 * Делимитер может быть задан через переменную окружения CACHE_ID_DELIMITER.
 * По умолчанию используется ':'.
 *
 * @package pribolshoy\repository
 */
class Config
{
    /**
     * Делимитер по умолчанию для строкового кеша.
     */
    protected const DEFAULT_STRING_DELIMITER = ':';

    /**
     * Делимитер по умолчанию для хэш-таблицы.
     */
    protected const DEFAULT_HASH_DELIMITER = '=';

    /**
     * Значение TTL по умолчанию для уменьшения времени кеширования (в секундах).
     * Используется для создания резерва времени перед истечением кеша.
     */
    protected const DEFAULT_CACHE_TTL_TO_MINUS = 2100; // 35 minutes (60 * 35)

    /**
     * Делимитер для разделения ключа кеша и ИД сущности (строка).
     * Инициализируется из переменной окружения или использует значение по умолчанию.
     *
     * @var string|null
     */
    protected static ?string $stringDelimiter = null;

    /**
     * Делимитер для разделения ключа кеша и ИД сущности (хэш-таблица).
     * Инициализируется из переменной окружения или использует значение по умолчанию.
     *
     * @var string|null
     */
    protected static ?string $hashDelimiter = null;

    /**
     * Значение TTL для уменьшения времени кеширования (в секундах).
     * Инициализируется из переменной окружения или использует значение по умолчанию.
     *
     * @var int|null
     */
    protected static ?int $cacheTtlToMinus = null;

    /**
     * Получить делимитер для ИД сущности (строка).
     * Читает значение из переменной окружения CACHE_ID_DELIMITER_STRING,
     * если она не установлена, использует значение по умолчанию ':'.
     *
     * @return string
     */
    public static function getStringDelimiter(): string
    {
        if (self::$stringDelimiter === null) {
            $envValue = getenv('CACHE_ID_DELIMITER_STRING');
            self::$stringDelimiter = $envValue !== false ? $envValue : self::DEFAULT_STRING_DELIMITER;
        }

        return self::$stringDelimiter;
    }

    /**
     * Получить делимитер для ИД сущности (хэш-таблица).
     * Читает значение из переменной окружения CACHE_ID_DELIMITER_HASH,
     * если она не установлена, использует значение по умолчанию ':'.
     *
     * @return string
     */
    public static function getHashDelimiter(): string
    {
        if (self::$hashDelimiter === null) {
            $envValue = getenv('CACHE_ID_DELIMITER_HASH');
            self::$hashDelimiter = $envValue !== false ? $envValue : self::DEFAULT_HASH_DELIMITER;
        }

        return self::$hashDelimiter;
    }

    /**
     * Получить делимитер для ИД сущности (обратная совместимость).
     * Использует делимитер для строкового кеша.
     *
     * @return string
     */
    public static function getIdDelimiter(): string
    {
        return self::getStringDelimiter();
    }

    /**
     * Установить делимитер для строкового кеша.
     * Переопределяет значение из переменной окружения.
     *
     * @param string $delimiter
     *
     * @return void
     */
    public static function setStringDelimiter(string $delimiter): void
    {
        self::$stringDelimiter = $delimiter;
    }

    /**
     * Установить делимитер для хэш-таблицы.
     * Переопределяет значение из переменной окружения.
     *
     * @param string $delimiter
     *
     * @return void
     */
    public static function setHashDelimiter(string $delimiter): void
    {
        self::$hashDelimiter = $delimiter;
    }

    /**
     * Установить делимитер для ИД сущности (обратная совместимость).
     * Устанавливает делимитер для строкового кеша.
     *
     * @param string $delimiter
     *
     * @return void
     */
    public static function setIdDelimiter(string $delimiter): void
    {
        self::$stringDelimiter = $delimiter;
    }

    /**
     * Получить значение TTL для уменьшения времени кеширования (в секундах).
     * Читает значение из переменной окружения CACHE_TTL_TO_MINUS,
     * если она не установлена, использует значение по умолчанию (35 минут = 2100 секунд).
     *
     * Используется для создания резерва времени перед истечением кеша,
     * чтобы обеспечить обновление данных до полного истечения срока действия.
     *
     * @return int Значение TTL в секундах
     */
    public static function getCacheTtlToMinus(): int
    {
        if (self::$cacheTtlToMinus === null) {
            $envValue = getenv('CACHE_TTL_TO_MINUS');
            if ($envValue !== false && is_numeric($envValue)) {
                self::$cacheTtlToMinus = (int) $envValue;
            } else {
                self::$cacheTtlToMinus = self::DEFAULT_CACHE_TTL_TO_MINUS;
            }
        }

        return self::$cacheTtlToMinus;
    }

    /**
     * Установить значение TTL для уменьшения времени кеширования (в секундах).
     * Переопределяет значение из переменной окружения.
     *
     * @param int $ttl Значение TTL в секундах
     *
     * @return void
     */
    public static function setCacheTtlToMinus(int $ttl): void
    {
        self::$cacheTtlToMinus = $ttl;
    }
}
