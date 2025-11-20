### Utils:

Utility classes that provide common functionality used across the project.

##### Config

Configuration class for cache-related settings. Provides centralized management of cache delimiters.

**Key features:**
- Manages string and hash delimiters for cache keys
- Reads configuration from environment variables
- Provides default values if environment variables are not set
- Static methods for easy access

**Methods:**
- `getStringDelimiter(): string` - Get delimiter for string cache keys (default: ':')
- `getHashDelimiter(): string` - Get delimiter for hash table cache keys (default: '=')
- `setStringDelimiter(string $delimiter): void` - Set string delimiter
- `setHashDelimiter(string $delimiter): void` - Set hash delimiter

**Environment variables:**
- `CACHE_ID_DELIMITER_STRING` - Override default string delimiter
- `CACHE_ID_DELIMITER_HASH` - Override default hash delimiter

**Use cases:**
- Consistent cache key formatting across the application
- Configurable delimiters for different environments
- Centralized cache configuration

**Example:**
```php
$delimiter = Config::getStringDelimiter(); // Returns ':'
$cacheKey = 'prefix' . Config::getStringDelimiter() . 'id'; // 'prefix:id'
```

##### Logger

Logging utility class for debugging and monitoring.

**Key features:**
- Logs messages with different levels
- Supports logging to file or buffer
- Provides structured logging for services, repositories, and filters

**Methods:**
- `log(string $action, string $key, string $type, $data = null)` - Log action with context
- `getLogs(): array` - Get all logged messages
- `clearLogs(): void` - Clear log buffer
- `writeToFile(string $filename): bool` - Write logs to file

**Log types:**
- `service` - Service-related logs
- `repository` - Repository-related logs
- `filter` - Filter-related logs

**Use cases:**
- Debugging cache operations
- Monitoring service behavior
- Tracking repository queries
- Performance analysis

**Example:**
```php
Logger::log('initStorage', $hashName, 'service', $items);
$logs = Logger::getLogs();
```

**Note:** Logger is primarily used for development and debugging. Consider using a proper logging framework (Monolog, PSR-3) for production environments.

