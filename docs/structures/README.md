### Structures:

Structures are used for organizing and storing data in memory. They provide different ways to access and manipulate items.

##### Abstract structure

Base abstract class for all structures. It implements `StructureInterface` and uses `UsedByServiceTrait` to maintain reference to the service that uses it.

**Key features:**
- Stores items in memory as an array
- Provides basic methods: `getItems()`, `setItems()`, `addItem()`, `getByKey()`, `getByKeys()`
- Uses composition with service through `UsedByServiceTrait`
- Supports dynamic parameter configuration via `addParams()`

**Main methods:**
- `getItems(): ?array` - Get all items
- `setItems(array $items)` - Set items array
- `addItem($item, $key = null)` - Add single item with optional key
- `getByKey($key)` - Get item by key
- `getByKeys(array $keys)` - Get items by array of keys

##### Array structure

Simple structure that stores items as a plain array. Items are accessed by their array keys.

**Use cases:**
- Simple sequential data storage
- When key-based access is not needed
- Default structure for services

**Characteristics:**
- Fast access by numeric or string keys
- No hashing overhead
- Direct array access

##### Hashtable structure

Structure that uses hash tables for key-based item access. It uses `HashableStructure` trait to generate MD5 hashes from keys.

**Key features:**
- Uses MD5 hashing for keys via `HashableStructure` trait
- Supports custom key names via `key_name` property
- Supports cursor keys for item identification
- Items are stored by hashed keys, not original keys

**Use cases:**
- When you need fast key-based lookups
- When keys are complex (arrays, objects)
- When you need to hash keys for consistent access

**Important:** Items are stored by hashed keys. When searching, keys are automatically hashed.

##### Hashtable collision structure

Advanced structure that handles hash collisions by organizing items in groups and collision keys.

**Key features:**
- Two-level organization: group keys and collision keys
- Handles multiple items with same hash
- Supports nested structure: `items[groupKey][collisionKey] = item`
- Uses `HashableStructure` trait for hashing

**Use cases:**
- When hash collisions are possible
- When you need to organize items by multiple criteria
- Complex data structures with hierarchical keys

**Structure:**
```
items[groupKey][collisionKey] = item
```

**Configuration:**
- `group_keys` - array of attribute names for grouping
- `collision_keys` - array of attribute names for collision handling

