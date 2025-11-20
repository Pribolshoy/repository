### Drivers:

Drivers provide abstraction layer for cache storage backends. They implement `CacheDriverInterface` and handle serialization/deserialization of data.

##### Abstract cache driver

Base abstract class for all cache drivers. It defines the contract for cache operations and provides common functionality.

**Key features:**
- Implements `CacheDriverInterface`
- Handles data serialization/unserialization
- Provides abstract methods that must be implemented by concrete drivers
- Configurable via constructor parameters

**Abstract methods (must be implemented):**
- `get(string $key, array $params = [])` - Retrieve value from cache
- `set(string $key, $value, int $cache_duration = 0, array $params = [])` - Store value in cache
- `delete(string $key, array $params = [])` - Delete value from cache

**Protected methods:**
- `serialize($data): string` - Serialize data for storage
- `unserialize(string $data)` - Unserialize data from storage

**Configuration:**
- `component` - Name of the cache component (default: 'redis')
- Constructor accepts array of parameters that are dynamically assigned to properties

**Use cases:**
- Abstracting cache storage implementation
- Supporting multiple cache backends (Redis, Memcached, etc.)
- Providing consistent interface for cache operations

**Note:** Concrete implementations should extend this class and implement abstract methods according to their specific cache backend.

