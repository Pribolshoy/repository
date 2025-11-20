<?php

namespace pribolshoy\repository\structures;

use pribolshoy\repository\interfaces\ServiceInterface;
use pribolshoy\repository\interfaces\StructureInterface;
use pribolshoy\repository\traits\HashableStructure;

/**
 * Class HashtableStructure
 *
 */
class HashtableStructure extends AbstractStructure
{
    use HashableStructure;

    /**
     * @var string|null
     */
    protected ?string $key_name = null;

    protected ?array $cursor_keys = null;

    /**
     * @param string|null $key_name
     *
     * @return HashtableStructure
     */
    public function setKeyName(?string $key_name): HashtableStructure
    {
        $this->key_name = $key_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getKeyName(): ?string
    {
        return $this->key_name;
    }

    /**
     * @param array|null $cursor_keys
     *
     * @return HashtableStructure
     */
    public function setCursorKeys(?array $cursor_keys): HashtableStructure
    {
        $this->cursor_keys = $cursor_keys;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCursorKeys(): ?array
    {
        return $this->cursor_keys;
    }

    /**
     * @override
     *
     * @param array $items
     *
     * @return StructureInterface
     */
    public function setItems(array $items): StructureInterface
    {
        $this->items = [];

        /** @var ServiceInterface $service */
        $service = $this->getService();

        foreach ($items as $key => $item) {
            // if structure use HashableStructure trait
            if (in_array(HashableStructure::class, class_uses(static::class)) ) {
                $itemKey = $this->getHash($item);
            } else {
                $itemKey = $service->getItemPrimaryKey($item);
            }

            // if set cursor keys, then item cursor will formed by them
            if (!is_null($cursorKeys = $this->getCursorKeys())) {
                $itemCursor = '';

                foreach ($cursorKeys as $attributeName) {
                    $itemCursor .= $service->getItemAttribute($item, $attributeName);
                }
            } else {
                // just position of item in items array
                $itemCursor = $key;
            }

            $this->addItem($itemCursor, $itemKey);
        }
        return $this;
    }
}

