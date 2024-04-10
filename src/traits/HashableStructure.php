<?php

namespace pribolshoy\repository\traits;

use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Trait HashableStructure
 *
 * Trait for helping functions for structures
 * storing hashed keys.
 */
trait HashableStructure
{
    /**
     * Get hash by item or item attribute using $service.
     *
     * @param string|array $value
     *
     * @return string
     */
    protected function getHash($value) :string
    {
        if (is_string($value) || is_int($value)) {
            $hash = $this->getHashByString($value);
        } else {
            $hash = $this->getHashByItem($value);
        }

        return $hash;
    }

    /**
     * Get hash by item attribute value.
     *
     * @param string $value
     *
     * @return string
     */
    private function getHashByString(string $value) :string
    {
        /** @var ServiceInterface $service */
        $service = $this->getService();

        return (string)$service->hash($value);
    }

    /**
     * Get hash by item.
     *
     * @param $value
     *
     * @return string
     */
    private function getHashByItem($value) :string
    {
        /** @var ServiceInterface $service */
        $service = $this->getService();

        $keyName = $this->getKeyName();

        if ($keyName
            && ($value = $service->getItemAttribute($value, $keyName))
        ) {
            $hash = $service->hash($value);
        } else {
            $hash = $service
                ->getItemHash($value);
        }

        return (string)$hash;
    }

}

