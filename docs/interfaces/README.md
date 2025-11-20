### Interfaces:

Interfaces define contracts that classes must implement. They ensure consistency and enable polymorphism.

##### Repository interfaces

**RepositoryInterface**
- Base interface for all repositories
- Defines methods for data access: `search()`, `getTableName()`, `makeQueryBuilder()`
- Used by services to interact with repositories

**CachebleRepositoryInterface**
- Extends `RepositoryInterface`
- Adds cache-related methods: `getFromCache()`, `setToCache()`, `deleteFromCache()`, `isCacheble()`
- Used by cacheable repositories

##### Service interfaces

**BaseServiceInterface**
- Base interface for all services
- Defines core service methods: `getList()`, `getBy()`, `getById()`, `getRepository()`, `getFilter()`
- Provides access to structures, filters, and repositories

**ServiceInterface**
- Extends `BaseServiceInterface`
- Adds service-specific methods
- Used by abstract service implementations

**CachebleServiceInterface**
- Extends `ServiceInterface`
- Adds cache-related methods: `initStorage()`, `clearStorage()`, `isCacheExists()`
- Used by cacheable services

**EnormousServiceInterface**
- Extends `CachebleServiceInterface`
- Adds methods for handling large datasets
- Used by enormous cacheable services

**PaginatedCachebleServiceInterface**
- Extends `CachebleServiceInterface`
- Adds pagination-related methods: `getPages()`, `setPages()`
- Used by paginated cacheable services

##### Filter interfaces

**FilterInterface**
- Base interface for all filters
- Defines filtering methods: `getList()`, `getBy()`, `getById()`, `getByExp()`, `getByMulti()`
- Used by services for data filtering

##### Structure interfaces

**StructureInterface**
- Base interface for all structures
- Defines structure methods: `getItems()`, `setItems()`, `addItem()`, `getByKey()`, `getByKeys()`
- Used by services to organize data

##### Driver interfaces

**CacheDriverInterface**
- Interface for cache drivers
- Defines cache operations: `get()`, `set()`, `delete()`
- Used by repositories for cache access

##### Other interfaces

**UsedByServiceInterface**
- Interface for classes that are used by services
- Defines `getService()` and `setService()` methods
- Used by structures and filters

**NotInitableService**
- Marker interface for services that cannot be initialized
- Located in `src/services/interfaces/`

**UnlistableService**
- Marker interface for services that don't support listing
- Located in `src/services/interfaces/`

**Design principles:**
- Interfaces define contracts, not implementations
- Classes implement interfaces to guarantee method availability
- Dependency injection uses interfaces, not concrete classes
- Enables polymorphism and testability

