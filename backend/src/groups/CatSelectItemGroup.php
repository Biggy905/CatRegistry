<?php

namespace CatRegistry\applications\groups;

use CatRegistry\applications\components\GroupInterface;
use CatRegistry\applications\entities\CatRegistry;

final class CatSelectItemGroup implements GroupInterface
{
    public function __construct(
        public CatRegistry $catRegistry
    ) {

    }

    public function toArray(): array
    {
        $mother = null;
        if (!empty($this->catRegistry->mother)) {
            $mother = new CatItemGroup($this->catRegistry->mother)->toArray();
        }

        $fathers = null;
        if (!empty($this->catRegistry->fathers)) {
            $fathers = new CatListGroup($this->catRegistry->fathers)->toArray();
        }

        return [
            'id' => $this->catRegistry->id,
            'name' => $this->catRegistry->name,
            'age' => $this->catRegistry->age,
            'gender' => $this->catRegistry->gender,
            'mother' => $mother,
            'fathers' => $fathers,
            'created_at' => $this->catRegistry->created_at,
            'updated_at' => $this->catRegistry->updated_at,
        ];
    }
}