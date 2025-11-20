<?php

namespace pribolshoy\repository\traits;

use pribolshoy\repository\interfaces\BaseServiceInterface;
use pribolshoy\repository\interfaces\ServiceInterface;

/**
 * Trait UsedByServiceTrait
 *
 * @package app\components\common\traits
 */
trait UsedByServiceTrait
{
    protected ?BaseServiceInterface $service = null;

    /**
     * @param BaseServiceInterface|null $service
     */
    public function setService(?BaseServiceInterface $service): void
    {
        $this->service = $service;
    }

    /**
     * @return BaseServiceInterface|ServiceInterface|null
     */
    public function getService(): ?object
    {
        return $this->service;
    }

}