<?php

namespace pribolshoy\repository\interfaces;

interface ServiceInterface
{
    public function isMultiplePrimaryKey(): bool;

    public function setPrimaryKeys(array $primaryKeys): object;

    public function setRepositoryClass(string $repository_class): object;

    public function getRepository(array $params = []): object;

    public function getList(array $params = [], bool $cache_to = true) : ?array;

    public function getItems();

    public function setItems(array $items);

    public function addItem($item, bool $replace_if_exists = true);

    public function updateHashtable();

    public function getHashtable();

    public function getHashByItem($item);

    public function getHashtableValue(string $hash);

    public function getItemByHash(string $hash);

    public function getItemPrimaryKey($item);

    public function hasItemAttribute($item, string $name): bool;

    public function getItemAttribute($item, string $name);

    public function getByHashtable($itemWithPrimaryKeys);

    public function setSorting(array $sorting): object;

    public function sort(array $items): array;

    public function resort(): object;
}

