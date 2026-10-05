<?php

namespace CatRegistry\tests\Unit\fixtures;

use CatRegistry\applications\entities\CatRegistry;
use yii\test\ActiveFixture;

final class CatRegistryFixture extends ActiveFixture
{
    /** @var string $modelClass */
    public $modelClass = CatRegistry::class;
    /** @var string $dataFile */
    public $dataFile = '@CatRegistry/tests/Unit/fixtures/data/cat_registries.php';
}
