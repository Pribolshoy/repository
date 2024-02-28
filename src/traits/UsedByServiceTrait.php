<?php

namespace pribolshoy\repository\traits;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\EnormousCachebleService;
use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Trait UsedByServiceTrait
 *
 * @package app\components\common\traits
 */
trait UsedByServiceTrait
{
    protected ?AbstractService $service = null;

    /**
     * @param AbstractService|null $service
     */
    public function setService(?AbstractService $service): void
    {
        $this->service = $service;
    }

    /**
     * @return ServiceInterface|EnormousCachebleService|null
     */
    public function getService(): ?object
    {
        return $this->service;
    }

}