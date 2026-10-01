<?php

namespace CatRegistry\applications\groups;

use CatRegistry\applications\components\GroupInterface;

final class CatListGroup implements GroupInterface
{
    public function __construct(
        public array $catRegistries
    )
    {

    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->catRegistries as $catRegistry) {
            $data[] = new CatItemGroup($catRegistry)->toArray();
        }

        return $data;
    }
}
