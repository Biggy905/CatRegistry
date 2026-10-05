<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\groups;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\groups\CatFilterItemGroup;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatFilterItemGroupTest extends Unit
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

        $group = new CatFilterItemGroup($cat);
        $data = $group->toArray();

        $this->assertSame(1, $data['id']);
        $this->assertSame('Barsik', $data['name']);
        $this->assertSame(3, $data['age']);
        $this->assertSame('male', $data['gender']);
        $this->assertSame('2025-01-01 00:00:00', $data['created_at']);
        $this->assertSame('2025-01-02 00:00:00', $data['updated_at']);
    }

    public function testMotherIsNullWhenNotLoaded(): void
    {
        $cat = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male');

        $group = new CatFilterItemGroup($cat);
        $data = $group->toArray();

        $this->assertNull($data['mother']);
    }

    public function testMotherIsIncludedWhenLoaded(): void
    {
        $mother = $this->makeCat(
            id: 100,
            name: 'Mila',
            age: 10,
            gender: 'female',
        );

        $kitten = $this->makeCat(
            id: 1,
            name: 'Barsik',
            age: 3,
            gender: 'male',
            motherId: 100,
        );
        $kitten->populateRelation('mother', $mother);

        $group = new CatFilterItemGroup($kitten);
        $data = $group->toArray();

        $this->assertIsArray($data['mother']);
        $this->assertSame(100, $data['mother']['id']);
        $this->assertSame('Mila', $data['mother']['name']);
        $this->assertSame(10, $data['mother']['age']);
        $this->assertSame('female', $data['mother']['gender']);
    }

    public function testMotherIsNullWhenRelationIsNull(): void
    {
        $cat = $this->makeCat(
            id: 1,
            name: 'Barsik',
            age: 3,
            gender: 'male',
            motherId: 100,
        );
        $cat->populateRelation('mother', null);

        $group = new CatFilterItemGroup($cat);
        $data = $group->toArray();

        $this->assertNull($data['mother']);
    }

    private function makeCat(
        int $id,
        string $name,
        int $age,
        string $gender,
        ?int $motherId = null,
        string $createdAt = '2025-01-01 00:00:00',
        ?string $updatedAt = null,
    ): CatRegistry {
        $cat = new CatRegistry();
        $cat->id = $id;
        $cat->name = $name;
        $cat->age = $age;
        $cat->gender = $gender;
        $cat->mother_id = $motherId;
        $cat->created_at = $createdAt;
        $cat->updated_at = $updatedAt;

        return $cat;
    }
}
