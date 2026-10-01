<?php

namespace CatRegistry\applications\groups;

use CatRegistry\applications\components\GroupInterface;
use CatRegistry\applications\entities\CatRegistry;

final class CatItemGroup implements GroupInterface
{
    public function __construct(
        public CatRegistry $catRegistry
    ) {

    }

    public function toArray(): array
    {
        return [
            'id' => $this->catRegistry->id,
            'name' => $this->catRegistry->name,
            'age' => $this->catRegistry->age,
            'gender' => $this->catRegistry->gender,
            'created_at' => $this->catRegistry->created_at,
            'updated_at' => $this->catRegistry->updated_at,
        ];
    }
}