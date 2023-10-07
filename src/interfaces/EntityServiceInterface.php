<?php

namespace pribolshoy\repository\interfaces;

interface EntityServiceInterface
{

    public function getItems();

    public function getHashtable();

    public function getHashValue(string $hash);

    public function getItemByHash(string $hash);
}

