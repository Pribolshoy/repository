<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\EnormousCachebleService;

interface UsedByServiceInterface
{
    /**
     * @param AbstractService|null $service
     */
    public function setService(?AbstractService $service): void;

    /**
     * @return ServiceInterface|EnormousCachebleService|null
     */
    public function getService(): ?object;
}

