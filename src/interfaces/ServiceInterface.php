<?php

namespace pribolshoy\repository\interfaces;

interface ServiceInterface extends BaseServiceInterface
{
    public function getItemAttribute($item, string $name);

    public function getByHashtable($key, ?string $structureName = null);

    public function sort(array $items): array;

    public function resort(): object;
}

