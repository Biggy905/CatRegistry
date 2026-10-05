<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\groups;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\groups\CatSelectItemGroup;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatSelectItemGroupTest extends Unit
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

        $group = new CatSelectItemGroup($cat);
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

        $group = new CatSelectItemGroup($cat);
        $data = $group->toArray();

        $this->assertSame(
            ['id', 'name', 'age', 'gender', 'mother', 'fathers', 'created_at', 'updated_at'],
            array_keys($data),
        );
    }

    public function testMotherAndFathersAreNullWhenNotLoaded(): void
    {
        $cat = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male');

        $group = new CatSelectItemGroup($cat);
        $data = $group->toArray();

        $this->assertNull($data['mother']);
        $this->assertNull($data['fathers']);
    }

    public function testMotherIsIncludedWhenLoaded(): void
    {
        $mother = $this->makeCat(id: 100, name: 'Mila', age: 10, gender: 'female');

        $kitten = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male', motherId: 100);
        $kitten->populateRelation('mother', $mother);

        $group = new CatSelectItemGroup($kitten);
        $data = $group->toArray();

        $this->assertIsArray($data['mother']);
        $this->assertSame(100, $data['mother']['id']);
        $this->assertSame('Mila', $data['mother']['name']);
        $this->assertSame(10, $data['mother']['age']);
        $this->assertSame('female', $data['mother']['gender']);
    }

    public function testFathersAreIncludedWhenLoaded(): void
    {
        $father1 = $this->makeCat(id: 200, name: 'Bars', age: 8, gender: 'male');
        $father2 = $this->makeCat(id: 201, name: 'Murzik', age: 9, gender: 'male');

        $kitten = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male');
        $kitten->populateRelation('fathers', [$father1, $father2]);

        $group = new CatSelectItemGroup($kitten);
        $data = $group->toArray();

        $this->assertIsArray($data['fathers']);
        $this->assertCount(2, $data['fathers']);
        $this->assertSame(200, $data['fathers'][0]['id']);
        $this->assertSame('Bars', $data['fathers'][0]['name']);
        $this->assertSame(201, $data['fathers'][1]['id']);
        $this->assertSame('Murzik', $data['fathers'][1]['name']);
    }

    public function testMotherAndFathersTogether(): void
    {
        $mother = $this->makeCat(id: 100, name: 'Mila', age: 10, gender: 'female');
        $father = $this->makeCat(id: 200, name: 'Bars', age: 8, gender: 'male');

        $kitten = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male', motherId: 100);
        $kitten->populateRelation('mother', $mother);
        $kitten->populateRelation('fathers', [$father]);

        $group = new CatSelectItemGroup($kitten);
        $data = $group->toArray();

        $this->assertIsArray($data['mother']);
        $this->assertSame(100, $data['mother']['id']);

        $this->assertIsArray($data['fathers']);
        $this->assertCount(1, $data['fathers']);
        $this->assertSame(200, $data['fathers'][0]['id']);
    }

    public function testFathersIsEmptyArrayWhenRelationIsEmpty(): void
    {
        $kitten = $this->makeCat(id: 1, name: 'Barsik', age: 3, gender: 'male');
        $kitten->populateRelation('fathers', []);

        $group = new CatSelectItemGroup($kitten);
        $data = $group->toArray();

        $this->assertNull($data['fathers']);
    }

    private function makeCat(
        int $id,
        string $name,
        int $age,
        string $gender,
        ?int $motherId = null,
        string $createdAt = '2025-01-01 00:00:00',
        ?string $updatedAt = '2025-01-01 00:00:00',
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
