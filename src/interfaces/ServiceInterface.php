<?php

namespace pribolshoy\repository\interfaces;

interface ServiceInterface extends BaseServiceInterface
{
    public function getItemPrimaryKey($item);

    public function getItemAttribute($item, string $name);

    public function getByHashtable($itemWithPrimaryKeys);

    public function sort(array $items): array;

    public function resort(): object;
}

