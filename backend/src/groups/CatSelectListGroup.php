<?php

namespace CatRegistry\applications\groups;

use CatRegistry\applications\components\GroupInterface;

final class CatSelectListGroup implements GroupInterface
{
    public function __construct(
        public array $catRegistries
    ) {

    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->catRegistries as $catRegistry) {
            $data[] = new CatSelectItemGroup($catRegistry)->toArray();
        }

        return $data;
    }
}
