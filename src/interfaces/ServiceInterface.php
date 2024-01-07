<?php

namespace pribolshoy\repository\interfaces;

interface ServiceInterface
{
    public function getList(array $params = [], bool $cache_to = true) : ?array;

    public function getItems();

    public function setItems(array $items);

    public function addItem($item, bool $replace_if_exists = true);

    public function getHashtable();

    public function getHashByItem($item);

    public function getHashValue(string $hash);

    public function getItemByHash(string $hash);
}

