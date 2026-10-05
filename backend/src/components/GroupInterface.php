<?php

namespace CatRegistry\applications\components;

interface GroupInterface
{
    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array;
}
