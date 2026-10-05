<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\entities;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\queries\CatRegistryQuery;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatRegistryTest extends Unit
{
    protected UnitTester $tester;

    public function testTableNameIsCatRegistries(): void
    {
        $this->assertSame('cat_registries', CatRegistry::tableName());
    }

    public function testFindReturnsCatRegistryQuery(): void
    {
        $this->assertInstanceOf(CatRegistryQuery::class, CatRegistry::find());
    }

    public function testNewInstanceHasNoId(): void
    {
        $cat = new CatRegistry();
        $this->assertNull($cat->id);
        $this->assertNull($cat->name);
        $this->assertNull($cat->age);
        $this->assertNull($cat->gender);
        $this->assertNull($cat->mother_id);
    }

    public function testGetMotherReturnsActiveQueryForCatRegistry(): void
    {
        $cat = new CatRegistry();
        $query = $cat->getMother();

        $this->assertInstanceOf(\yii\db\ActiveQuery::class, $query);
        $this->assertSame(CatRegistry::class, $query->modelClass);
    }

    public function testGetFathersReturnsActiveQueryForCatRegistry(): void
    {
        $cat = new CatRegistry();
        $query = $cat->getFathers();

        $this->assertInstanceOf(\yii\db\ActiveQuery::class, $query);
        $this->assertSame(CatRegistry::class, $query->modelClass);
    }
}
