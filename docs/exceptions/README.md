### Exceptions:

Custom exception classes for better error handling and debugging. They extend PHP's base `Exception` class.

##### RepositoryException

Exception class for repository-related errors.

**Use cases:**
- Database connection errors
- Query execution failures
- Invalid repository configuration
- Data access violations

**Example:**
```php
throw new RepositoryException('Failed to execute query');
```

**Characteristics:**
- Extends `\Exception`
- Can be caught specifically for repository errors
- Provides clear error context for repository operations

##### ServiceException

Exception class for service-related errors.

**Use cases:**
- Service configuration errors
- Invalid service state
- Missing required properties (e.g., `repository_class`)
- Structure initialization failures
- Invalid item operations

**Example:**
```php
throw new ServiceException('Property repository_class is not set');
```

**Characteristics:**
- Extends `\Exception`
- Can be caught specifically for service errors
- Used throughout service layer for error reporting

**Common scenarios:**
- Missing repository class configuration
- Structure class not found
- Invalid item structure
- Service initialization failures

**Best practices:**
- Use specific exception types for better error handling
- Include descriptive error messages
- Catch exceptions at appropriate levels (service, controller, etc.)

