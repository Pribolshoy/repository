### Traits:

Traits provide reusable functionality that can be used across multiple classes. They help reduce code duplication and maintain consistency.

##### UsedByServiceTrait

Trait that provides service reference management for classes that are used by services.

**Key features:**
- Maintains reference to `BaseServiceInterface` instance
- Provides getter and setter for service
- Used by structures and filters to access their parent service

**Methods:**
- `setService(?BaseServiceInterface $service): void` - Set service reference
- `getService(): ?object` - Get service reference (returns `BaseServiceInterface|ServiceInterface|null`)

**Use cases:**
- Structures need access to service for item operations
- Filters need access to service for data retrieval
- Any class that needs to communicate with its parent service

**Used by:**
- `AbstractStructure` and all structure implementations
- `AbstractFilter` and all filter implementations

##### HashableStructure

Trait that provides hashing functionality for structures. It generates MD5 hashes from various input types.

**Key features:**
- Generates MD5 hashes from strings, integers, arrays, or objects
- Uses service's `getItemAttribute()` method to extract values
- Supports custom attribute names for hashing

**Methods:**
- `getHash($value): string` - Generate hash from value (string, int, array, or object)
- `getHashByString(string $value): string` - Generate hash from string
- `getHashByItem($item): string` - Generate hash from item (array or object)

**Use cases:**
- Hashtable structures need to hash keys for storage
- Consistent key generation for cache lookups
- Converting complex keys to simple hash strings

**Used by:**
- `HashtableStructure`
- `HashtableCollisionStructure`

**Note:** The trait uses the service's `getItemAttribute()` method, so it requires `UsedByServiceTrait` to be used together.

##### CatalogTrait

Trait that provides pagination functionality. It integrates with Yii2 Pagination component.

**Key features:**
- Manages pagination state
- Provides access to pagination object
- Used by repositories and services that need pagination

**Dependencies:**
- Requires `yii\data\Pagination` from Yii2 framework

**Use cases:**
- Repositories that need to paginate query results
- Services that work with paginated data
- Displaying large datasets in pages

**Note:** This trait creates a dependency on Yii2 framework. Consider abstracting pagination if you want to remove framework dependency.

**Used by:**
- `AbstractCachebleRepository`
- Services that need pagination support

