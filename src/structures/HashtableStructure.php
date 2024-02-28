<?php

namespace pribolshoy\repository\structures;

use pribolshoy\repository\interfaces\StructureInterface;

/**
 * Class HashtableStructure
 *
 */
class HashtableStructure extends AbstractStructure
{
    /**
     * @override
     *
     * @param array $items
     *
     * @return StructureInterface
     */
    public function setItems(array $items)
    {
        $this->setItems([]);
        foreach ($items as $key => $item) {
            $hash = $this->getService()->getItemHash($item);
            $this->addItem($key, $hash);
        }
        return $this;
    }
}

