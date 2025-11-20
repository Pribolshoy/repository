# Архитектура проекта

## Обзор

Проект реализует паттерн Repository с поддержкой кэширования и фильтрации данных. Архитектура построена на принципах слоистой структуры с четким разделением ответственности.

## Структура документации

Документация по архитектуре организована по компонентам:

- **[Services](services/README.md)** - Сервисы для бизнес-логики
- **[Repositories](repositories/README.md)** - Репозитории для доступа к данным
- **[Filters](filters/README.md)** - Фильтры для выборки данных
- **[Structures](structures/README.md)** - Структуры данных для организации информации
- **[Drivers](drivers/README.md)** - Драйверы кэша для работы с хранилищами
- **[Traits](traits/README.md)** - Трейты для переиспользуемой функциональности
- **[Exceptions](exceptions/README.md)** - Исключения для обработки ошибок
- **[Utils](utils/README.md)** - Утилиты для общих функций
- **[Interfaces](interfaces/README.md)** - Интерфейсы для определения контрактов

## Слои архитектуры

Проект организован в следующие слои (согласно deptrac):

### 1. Exception (базовый слой)
Исключения для обработки ошибок:
- `RepositoryException` - исключения для репозиториев
- `ServiceException` - исключения для сервисов

### 2. Interface (контракты)
Интерфейсы определяют контракты для всех компонентов:
- Repository interfaces (`RepositoryInterface`, `CachebleRepositoryInterface`)
- Service interfaces (`BaseServiceInterface`, `ServiceInterface`, `CachebleServiceInterface`, etc.)
- Filter interfaces (`FilterInterface`)
- Structure interfaces (`StructureInterface`)
- Driver interfaces (`CacheDriverInterface`)

### 3. Trait (переиспользуемая функциональность)
Трейты предоставляют общую функциональность:
- `UsedByServiceTrait` - управление ссылкой на сервис
- `HashableStructure` - хеширование ключей
- `CatalogTrait` - пагинация

### 4. Structure (структуры данных)
Структуры для организации данных в памяти:
- `AbstractStructure` - базовая структура
- `ArrayStructure` - простая массивная структура
- `HashtableStructure` - хеш-таблица
- `HashtableCollisionStructure` - хеш-таблица с коллизиями

### 5. Driver (драйверы кэша)
Абстракция для работы с кэшем:
- `AbstractCacheDriver` - базовый драйвер кэша

### 6. Repository (репозитории)
Доступ к данным:
- `AbstractRepository` - базовый репозиторий
- `AbstractCachebleRepository` - репозиторий с кэшированием

### 7. Filter (фильтры)
Фильтрация данных:
- `AbstractFilter` - базовый фильтр
- `ServiceFilter` - фильтр для сервисов
- `CachebleServiceFilter` - фильтр для кэшируемых сервисов
- `EnormousServiceFilter` - фильтр для больших сервисов
- `PaginatedServiceFilter` - фильтр для пагинируемых сервисов

### 8. Service (сервисы)
Бизнес-логика:
- `BaseService` - базовый сервис
- `AbstractService` - абстрактный сервис
- `AbstractCachebleService` - кэшируемый сервис
- `EnormousCachebleService` - сервис для больших данных
- `PaginatedCachebleService` - пагинируемый сервис

### 9. Util (утилиты)
Вспомогательные классы:
- `Config` - конфигурация
- `Logger` - логирование

## Иерархия зависимостей

```
Exception (базовый слой)
    ↑
Interface → Exception, Structure
    ↑
Trait → Trait, Interface, Exception
    ↑
Structure → Structure, Trait, Interface, Exception
    ↑
Driver → Driver, Interface, Exception
    ↑
Repository → Repository, Interface, Exception, Trait, Util
    ↑
Filter → Filter, Service, Structure, Trait, Interface, Exception
    ↑
Service → Service, Repository, Filter, Structure, Trait, Driver, Interface, Exception, Util
    ↑
Util → Util, Exception
```

## Принципы проектирования

### Dependency Inversion Principle
- Классы зависят от интерфейсов, а не от конкретных реализаций
- Все зависимости используют интерфейсы (`CachebleRepositoryInterface`, `FilterInterface`, etc.)

### Single Responsibility Principle
- Каждый класс имеет одну ответственность
- Repositories - доступ к данным
- Services - бизнес-логика
- Filters - фильтрация данных
- Structures - организация данных

### Composition over Inheritance
- Использование композиции для гибкости
- Services используют Filters и Structures через композицию
- Traits используются для переиспользования кода

### Separation of Concerns
- Четкое разделение между слоями
- Каждый слой имеет свою ответственность
- Минимальные зависимости между слоями

## Паттерны проектирования

### Repository Pattern
Инкапсулирует логику доступа к данным, предоставляя более объектно-ориентированный интерфейс.

### Service Layer Pattern
Сервисы содержат бизнес-логику и координируют работу репозиториев, фильтров и структур.

### Strategy Pattern
Различные типы фильтров и структур могут быть заменены динамически.

### Template Method Pattern
Абстрактные классы определяют общий алгоритм, а конкретные классы реализуют детали.

## Дополнительная документация

- [Deptrac](deptrac.md) - Анализ зависимостей между слоями
- [Issues](issues.md) - Проблемные места и рекомендации
- [Testing](../tests/README.md) - Документация по тестированию

