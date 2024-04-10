<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\AbstractService;
use pribolshoy\repository\EnormousCachebleService;

interface UsedByServiceInterface
{
    /**
     * @param BaseServiceInterface|ServiceInterface|null $service
     */
    public function setService(?AbstractService $service): void;

    /**
     * @return BaseServiceInterface|ServiceInterface|EnormousCachebleService|null
     */
    public function getService(): ?object;
}

