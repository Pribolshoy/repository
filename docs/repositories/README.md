### Repositories:

Repositories are divided into two main groups - one uses cache, the other does not. They encapsulate data access logic and provide a clean interface for querying data.

##### Abstract repository

Base abstract class for all repositories. It implements `RepositoryInterface` and provides core functionality for data access.

**Key features:**
- Encapsulates database query building and execution
- Supports filtering, pagination, and lazy loading
- Provides methods for building queries: `makeQueryBuilder()`, `collectFilter()`, `addQueries()`
- Manages query parameters and filters

**Main methods:**
- `search(bool $refresh = false): ?array` - Execute search query and return results
- `getTableName(): string` - Get table name for queries
- `makeQueryBuilder()` - Initialize query builder object
- `collectFilter()` - Collect filter parameters from request
- `getTotalCount(): ?int` - Get total count of matching records

**Configuration:**
- `model_class` - Class name of the model/ActiveRecord
- `lazy_load` - Enable lazy loading of relations
- `need_total` - Whether to calculate total count
- `is_array` - Return results as array instead of objects

**Use cases:**
- Direct database access without caching
- Simple data retrieval operations
- When caching is not needed or not available

##### Abstract cacheble repository

Extends `AbstractRepository` and adds cache functionality. It implements `CachebleRepositoryInterface` and uses cache drivers for data storage.

**Key features:**
- All features of `AbstractRepository`
- Cache integration via cache drivers
- Hash name management for cache keys
- Cache duration and expiration handling
- Active cache flag to enable/disable caching

**Cache-related methods:**
- `getFromCache(bool $refresh = false, array $params = [])` - Retrieve data from cache
- `setToCache($value, array $params = [])` - Store data in cache
- `deleteFromCache(array $params = [])` - Delete data from cache
- `isCacheble(): bool` - Check if caching is enabled
- `getHashName(bool $refresh = false, bool $use_params = true): string` - Get cache hash name
- `setHashName(string $hash_name)` - Set cache hash name

**Configuration:**
- `driver` - Cache driver name (default: 'redis')
- `driver_path` - Path to driver class
- `driver_params` - Parameters for cache driver
- `active_cache` - Enable/disable caching
- `hash_name` - Current cache hash name
- `cache_duration` - Cache expiration time in seconds (default: 10800 = 3 hours)
- `max_cached_page` - Maximum number of pages to cache (default: 4)

**Use cases:**
- Data access with caching support
- High-performance data retrieval
- Reducing database load
- Fast data access for frequently queried data

**Cache strategy:**
- Hash names are generated from table name and query parameters
- Cache duration can be customized per repository
- Supports cache invalidation and refresh
- Can disable caching dynamically via `active_cache` flag