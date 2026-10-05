<?php

namespace CatRegistry\applications\groups;

use CatRegistry\applications\components\GroupInterface;
use CatRegistry\applications\entities\CatRegistry;

final class CatListGroup implements GroupInterface
{
    /**
     * @param CatRegistry[] $catRegistries
     */
    public function __construct(
        public array $catRegistries
    ) {

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
