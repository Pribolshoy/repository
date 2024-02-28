<?php

namespace pribolshoy\repository\interfaces;

use pribolshoy\repository\filters\AbstractFilter;
use pribolshoy\repository\structures\HashtableStructure;

interface BaseServiceInterface
{
    public function isMultiplePrimaryKey(): bool;

    public function setPrimaryKeys(array $primaryKeys): object;

    public function getItemStructure(bool $refresh = false):StructureInterface;

    public function getItemHashtableStructure(bool $refresh = false):HashtableStructure;

    public function getRepository(array $params = []): object;

    public function setRepositoryClass(string $repository_class): object;

    public function getItems():?array;

    public function setItems(array $items);

    public function addItem($item, bool $replace_if_exists = true) : object;

    public function getItemHash($item);

    public function getItemPrimaryKey($item);

    public function getHashtable();

    public function updateHashtable();

    public function setFilterClass(string $filter_class): object;

    public function getFilter(bool $refresh = false): AbstractFilter;

    public function setSorting(array $sorting): object;

    public function getList(array $params = [], bool $cache_to = true) : ?array;

    public function getByExp(array $attributes);

    public function getByMulti(array $attributes);

    public function getBy(array $attributes);

    public function getById(int $id, array $attributes = []);

    public function getByIds(array $ids, array $attributes = []);
}

