<?php

use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use CatRegistry\applications\repositories\databases\CatRegistryRepository;

return [
    CatRegistryRepositoryInterface::class => CatRegistryRepository::class,
];
