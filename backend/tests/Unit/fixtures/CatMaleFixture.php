<?php

namespace CatRegistry\tests\Unit\fixtures;

use CatRegistry\applications\entities\CatMale;
use yii\test\ActiveFixture;

final class CatMaleFixture extends ActiveFixture
{
    /** @var string $modelClass */
    public $modelClass = CatMale::class;
    /** @var string $dataFile */
    public $dataFile = '@CatRegistry/tests/Unit/fixtures/data/cat_males.php';
    /** @var array<int, string> $depends */
    public $depends = [
        CatRegistryFixture::class
    ];
}
