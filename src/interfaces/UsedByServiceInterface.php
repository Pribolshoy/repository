<?php

namespace pribolshoy\repository\interfaces;

interface UsedByServiceInterface
{
    /**
     * @param BaseServiceInterface|ServiceInterface|null $service
     */
    public function setService(?BaseServiceInterface $service): void;

    /**
     * @return BaseServiceInterface|ServiceInterface|null
     */
    public function getService(): ?object;
}

