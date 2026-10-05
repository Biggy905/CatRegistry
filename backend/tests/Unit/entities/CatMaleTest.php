<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\entities;

use CatRegistry\applications\entities\CatMale;
use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\queries\CatMaleQuery;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatMaleTest extends Unit
{
    protected UnitTester $tester;

    public function testTableNameIsCatMales(): void
    {
        $this->assertSame('cat_males', CatMale::tableName());
    }

    public function testFindReturnsCatMaleQuery(): void
    {
        $this->assertInstanceOf(CatMaleQuery::class, CatMale::find());
    }

    public function testNewInstanceHasNoId(): void
    {
        $male = new CatMale();
        $this->assertNull($male->id);
        $this->assertNull($male->cat_id);
        $this->assertNull($male->father_id);
    }

    public function testGetChildrenReturnsActiveQueryForCatRegistry(): void
    {
        $male = new CatMale();
        $query = $male->getChildren();

        $this->assertInstanceOf(\yii\db\ActiveQuery::class, $query);
        $this->assertSame(CatRegistry::class, $query->modelClass);
    }
}
