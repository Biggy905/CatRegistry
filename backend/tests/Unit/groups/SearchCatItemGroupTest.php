<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\groups;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\groups\SearchCatItemGroup;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class SearchCatItemGroupTest extends Unit
{
    protected UnitTester $tester;

    public function testToArrayReturnsAllFields(): void
    {
        $cat = $this->makeCat(
            id: 1,
            name: 'Barsik',
            age: 3,
            gender: 'male',
            createdAt: '2025-01-01 00:00:00',
            updatedAt: '2025-01-02 00:00:00',
        );

        $group = new SearchCatItemGroup($cat);
        $data = $group->toArray();

        $this->assertSame(1, $data['id']);
        $this->assertSame('Barsik', $data['name']);
        $this->assertSame(3, $data['age']);
        $this->assertSame('male', $data['gender']);
        $this->assertSame('2025-01-01 00:00:00', $data['created_at']);
        $this->assertSame('2025-01-02 00:00:00', $data['updated_at']);
    }

    public function testToArrayHasExpectedKeys(): void
    {
        $cat = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male');

        $group = new SearchCatItemGroup($cat);
        $data = $group->toArray();

        $this->assertSame(
            ['id', 'name', 'age', 'gender', 'created_at', 'updated_at'],
            array_keys($data),
        );
    }

    public function testNullUpdatedAtIsReturned(): void
    {
        $cat = $this->makeCat(
            id: 1,
            name: 'Barsik',
            age: 3,
            gender: 'male',
            updatedAt: null,
        );

        $group = new SearchCatItemGroup($cat);
        $data = $group->toArray();

        $this->assertNull($data['updated_at']);
    }

    private function makeCat(
        int $id,
        string $name,
        int $age,
        string $gender,
        string $createdAt = '2025-01-01 00:00:00',
        ?string $updatedAt = '2025-01-01 00:00:00',
    ): CatRegistry {
        $cat = new CatRegistry();
        $cat->id = $id;
        $cat->name = $name;
        $cat->age = $age;
        $cat->gender = $gender;
        $cat->created_at = $createdAt;
        $cat->updated_at = $updatedAt;

        return $cat;
    }
}
