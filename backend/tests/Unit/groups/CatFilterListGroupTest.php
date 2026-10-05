<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\groups;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\groups\CatFilterListGroup;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatFilterListGroupTest extends Unit
{
    protected UnitTester $tester;

    public function testEmptyListReturnsEmptyArray(): void
    {
        $group = new CatFilterListGroup([]);

        $this->assertSame([], $group->toArray());
    }

    public function testListReturnsAllItems(): void
    {
        $cats = [
            $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male'),
            $this->makeCat(id: 2, name: 'Mila', age: 5, gender: 'female'),
            $this->makeCat(id: 3, name: 'Murzik', age: 4, gender: 'male'),
        ];

        $group = new CatFilterListGroup($cats);
        $data = $group->toArray();

        $this->assertCount(3, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame(2, $data[1]['id']);
        $this->assertSame(3, $data[2]['id']);
    }

    public function testListReturnsSequentialKeys(): void
    {
        $cats = [
            $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male'),
            $this->makeCat(id: 2, name: 'Mila', age: 5, gender: 'female'),
        ];

        $group = new CatFilterListGroup($cats);
        $data = $group->toArray();

        $this->assertSame([0, 1], array_keys($data));
    }

    public function testEachItemHasExpectedFields(): void
    {
        $cats = [
            $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male'),
        ];

        $group = new CatFilterListGroup($cats);
        $data = $group->toArray();

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('age', $data[0]);
        $this->assertArrayHasKey('gender', $data[0]);
        $this->assertArrayHasKey('mother', $data[0]);
        $this->assertArrayHasKey('created_at', $data[0]);
        $this->assertArrayHasKey('updated_at', $data[0]);
    }

    private function makeCat(
        int $id,
        string $name,
        int $age,
        string $gender,
    ): CatRegistry {
        $cat = new CatRegistry();
        $cat->id = $id;
        $cat->name = $name;
        $cat->age = $age;
        $cat->gender = $gender;
        $cat->created_at = '2025-01-01 00:00:00';

        return $cat;
    }
}
