<?php

namespace pribolshoy\repository\services;

use pribolshoy\repository\interfaces\RepositoryInterface;
use pribolshoy\repository\filters\PaginatedServiceFilter;
use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\CachebleRepositoryInterface;
use pribolshoy\repository\interfaces\CachebleServiceInterface;
use pribolshoy\repository\interfaces\PaginatedCachebleServiceInterface;
use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\Logger;

/**
 * Class PaginatedCachedService
 *
 * Use for caching of entities IDs of filtered
 * pagination list.
 *
 * @package app\services
 */
abstract class PaginatedCachebleService extends AbstractCachebleService implements PaginatedCachebleServiceInterface
{
    protected string $hash_prefix = 'list:';

    public string $pagination_prefix = 'pagination:';

    public array $cache_params = [
        'get' => [
            'strategy' => 'string'
        ],
        'set' => [
            'strategy' => 'string'
        ]
    ];

    protected string $filter_class = PaginatedServiceFilter::class;

    /**
     * @return string
     */
    public function getPaginationHashPrefix(): string
    {
        return $this->pagination_prefix;
    }

    /**
     * For paginated services initiation items to cache storage
     * is not obvious operation, and it can't be unified.
     * By default, it is stubbed.
     *
     * @param RepositoryInterface|null $repository
     * @param bool $refresh_repository_cache
     *
     * @return mixed
     * @throws \Exception
     */
    public function initStorage(?RepositoryInterface $repository = null, bool $refresh_repository_cache = false): CachebleServiceInterface
    {
        return $this;
    }

    /**
     * Delete all pagination cache from storage by hash.
     *
     * @param CachebleRepositoryInterface|null $repository
     * @param array $params
     *
     * @return bool
     * @throws \Exception
     */
    public function clearStorage(?CachebleRepositoryInterface $repository = null, array $params = []): bool
    {
        /** @var CachebleRepositoryInterface $repository */
        if (!$repository) {
            $repository = $this->getRepository($params);
        }

        // entities
        $entitiesHashName = parent::getHashPrefix() . $repository->getHashPrefix();
        $repository
            ->setHashName($entitiesHashName)
            ->deleteFromCache();

        Logger::log('clearStorage', $entitiesHashName, 'service');

        // pagination
        $paginationHashName = $this->getPaginationHashPrefix()
            . $repository->getHashPrefix();
        $repository
            ->setHashName($paginationHashName)
            ->deleteFromCache();

        Logger::log('clearStorage', $paginationHashName, 'service');

        return true;
    }

    /**
     * Получение элементов по массиву ID
     *
     * Переопределяет базовый метод для использования сервиса элементов,
     * полученного через getItemService(), вместо фильтра.
     *
     * @param array $ids Массив ID элементов для получения
     * @param array $attributes Дополнительные атрибуты для фильтрации
     *
     * @return array Массив элементов, индексированный по ID
     * @throws ServiceException Если getItemService() не переопределен
     * @throws \Exception При ошибке получения элементов
     */
    public function getByIds(array $ids, array $attributes = []): array
    {
        $itemService = $this->getItemService();

        return $itemService->getByIds($ids, $attributes) ?? [];
    }

    /**
     * Получение сервиса для работы с элементами
     *
     * Метод должен быть переопределен в наследниках для возврата конкретного сервиса.
     * Если метод не переопределен, выбрасывается исключение.
     *
     * @return BaseServiceInterface Сервис для работы с элементами
     * @throws ServiceException Если метод не переопределен в наследнике
     */
    protected function getItemService(): BaseServiceInterface
    {
        throw new ServiceException(
            'Method ' . __METHOD__ . ' must be overridden in child class ' . static::class
        );
    }
}
