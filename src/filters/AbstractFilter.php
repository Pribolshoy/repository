<?php

namespace pribolshoy\repository\filters;

use pribolshoy\repository\exceptions\ServiceException;
use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\FilterInterface;
use pribolshoy\repository\interfaces\UsedByServiceInterface;
use pribolshoy\repository\traits\UsedByServiceTrait;

/**
 * Class AbstractFilter
 *
 */
abstract class AbstractFilter implements FilterInterface
{
    use UsedByServiceTrait;

    public function __construct(BaseServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getList(array $params = [], bool $cache_to = true): ?array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByExp(array $attributes): array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByMulti(array $attributes): array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getBy(array $attributes)
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param int $id
     * @param array $attributes
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function getById($id, array $attributes = [])
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * @param array $ids
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function getByIds(array $ids, array $attributes = []): array
    {
        throw new \Exception('Method ' . __METHOD__ . ' is not realized!');
    }

    /**
     * Проверяет, соответствует ли элемент заданным атрибутам фильтрации.
     *
     * Метод выполняет проверку всех указанных атрибутов:
     * - Если массив атрибутов пуст, элемент считается соответствующим (возвращает true)
     * - Для каждого атрибута проверяется:
     *   1. Получается значение атрибута из элемента через сервис
     *   2. Если значение атрибута отсутствует (null, false, пустая строка) - элемент не проходит фильтр
     *   3. Если значение атрибута не входит в список допустимых значений - элемент не проходит фильтр
     * - Если все атрибуты прошли проверку - элемент соответствует фильтру (возвращает true)
     *
     * Примеры использования:
     * - filterByAttributes($item, ['name' => 'John']) - проверяет, что name == 'John'
     * - filterByAttributes($item, ['status' => ['active', 'pending']]) - проверяет, что status входит в список
     * - filterByAttributes($item, ['age' => 25, 'city' => 'Moscow']) - проверяет оба условия (логическое И)
     * - filterByAttributes($item, []) - всегда возвращает true (нет условий фильтрации)
     *
     * @param mixed $item Элемент для проверки (массив или объект)
     * @param array $attributes Атрибуты для фильтрации в формате ['attribute_name' => value]
     *                          Значение может быть строкой или массивом допустимых значений
     *
     * @return bool true - элемент соответствует всем атрибутам, false - не соответствует
     * @throws ServiceException Если сервис не установлен или произошла ошибка при получении атрибута
     */
    public function filterByAttributes($item, array $attributes): bool
    {
        // Если атрибуты не заданы, элемент считается соответствующим фильтру
        if ($attributes) {
            // Проверяем каждый атрибут из списка фильтрации
            foreach ($attributes as $name => $value) {
                // Если значение атрибута - строка, преобразуем в массив для единообразной обработки
                // Это позволяет использовать как ['status' => 'active'], так и ['status' => ['active', 'pending']]
                if (is_string($value)) {
                    $value = [$value];
                }

                // Получаем значение атрибута из элемента через сервис
                // getItemAttribute может работать как с массивами, так и с объектами
                $itemAttrValue = $this->getService()
                    ->getItemAttribute($item, $name);

                // Проверяем условие: элемент НЕ проходит фильтр, если:
                // 1. Значение атрибута отсутствует (null, false, пустая строка) ИЛИ
                // 2. Значение атрибута не входит в список допустимых значений
                if (!$itemAttrValue
                    || !in_array($itemAttrValue, $value)
                ) {
                    // Элемент не соответствует хотя бы одному атрибуту - возвращаем false
                    return false;
                }
            }
        }

        // Все атрибуты прошли проверку (или атрибуты не заданы) - элемент соответствует фильтру
        return true;
    }
}

