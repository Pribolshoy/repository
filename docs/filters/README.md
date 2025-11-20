### Filters:

Each service has its own filter which it uses for item selection. Filters use composition pattern, so you can dynamically change filters in service object, but it's not recommended.

##### Abstract filter

Base abstract class for all filters. It implements `FilterInterface` and uses `UsedByServiceTrait` to access the parent service.

**Key features:**
- Provides filtering methods that delegate to service
- Uses composition with service through `UsedByServiceTrait`
- Defines abstract methods that must be implemented by concrete filters
- Acts as a bridge between service and data access logic

**Abstract methods (must be implemented):**
- `getList(array $params = [], bool $cache_to = true): ?array` - Get list of items
- `getByExp(array $attributes)` - Get items by expression
- `getByMulti(array $attributes)` - Get items by multiple attributes
- `getBy(array $attributes)` - Get single item by attributes
- `getById(int $id, array $attributes = [])` - Get item by ID
- `getByIds(array $ids, array $attributes = [])` - Get items by array of IDs

**Use cases:**
- Encapsulating filtering logic
- Separating concerns between service and data access
- Enabling different filtering strategies per service type

##### Service filter

Filter for basic services without caching. It works with `AbstractService` and uses repository directly.

**Key features:**
- Simple filtering without cache considerations
- Direct repository access
- Calls `initStorage()` on service if items are not set
- Returns items from service's memory

**Use cases:**
- Services that don't use caching
- Simple data retrieval operations
- When cache overhead is not needed

##### Cacheble service filter

Filter for cacheable services. It extends `ServiceFilter` and adds cache-aware filtering.

**Key features:**
- All features of `ServiceFilter`
- Cache-aware data retrieval
- Supports alias-based lookups via `getByAlias()`
- Handles cache initialization and refresh
- Uses `CachebleRepositoryInterface` for cache operations

**Additional methods:**
- `getByAlias(string $alias, array $attributes = [])` - Get item by alias
- `getPrimaryKeyByAlias(string $alias, ?CachebleRepositoryInterface $repository = null)` - Get primary key by alias

**Use cases:**
- Cacheable services with up to ~1000 rows
- Services that benefit from cache lookups
- Fast data access with cache support

##### Enormous service filter

Filter for enormous cacheable services. It extends `CachebleServiceFilter` and handles large datasets.

**Key features:**
- All features of `CachebleServiceFilter`
- Optimized for large datasets (thousands of rows)
- Items are stored in cache but not kept in memory
- Supports ID-based lookups via `getByIds()`
- Throws exceptions for unsupported methods (`getList()`, `getByExp()`, etc.)

**Restrictions:**
- `getList()` - Not supported (throws exception)
- `getByExp()` - Not supported (throws exception)
- `getByMulti()` - Not supported (throws exception)
- `getBy()` - Not supported (throws exception)

**Supported methods:**
- `getById(int $id, array $attributes = [])` - Get single item by ID
- `getByIds(array $ids, array $attributes = [])` - Get multiple items by IDs
- `getByAlias(string $alias, array $attributes = [])` - Get item by alias

**Use cases:**
- Large datasets (thousands+ rows)
- ID-based lookups only
- Memory-efficient data access
- Cache-only storage strategy

##### Paginated service filter

Filter for paginated cacheable services. It extends `AbstractFilter` and handles pagination.

**Key features:**
- Pagination-aware filtering
- Caches pagination metadata, not items
- Uses hash of query parameters for cache keys
- Returns paginated results with page information

**Key differences:**
- Caches IDs of items, not items themselves
- Stores pagination information separately
- Items are fetched by IDs after pagination
- Optimized for paginated list views

**Use cases:**
- Paginated list views
- Large datasets with pagination
- When you need page navigation
- List views with filtering and sorting

**Note:** `getByIds()` is not supported and throws exception, as paginated services work differently.