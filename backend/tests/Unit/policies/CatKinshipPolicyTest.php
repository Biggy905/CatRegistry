<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\policies;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\exceptions\BadRequestHttpException;
use CatRegistry\applications\policies\CatKinshipPolicy;
use CatRegistry\applications\repositories\CatRegistryRepositoryInterface;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatKinshipPolicyTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // ensureNoSelfParenting
    // ---------------------------------------------------------------------

    public function testCreateWithoutCatIdSkipsSelfParentingCheck(): void
    {
        $policy = $this->makePolicy();

        // не должно быть исключений
        $policy->ensureCanAssignParents(null, null, []);

        $this->assertTrue(true);
    }

    public function testMotherEqualsCatIdThrows(): void
    {
        $policy = $this->makePolicy();

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: 5, motherId: 5, fatherIds: []);
    }

    public function testFatherEqualsCatIdThrows(): void
    {
        $policy = $this->makePolicy();

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: 5, motherId: null, fatherIds: [5]);
    }

    // ---------------------------------------------------------------------
    // ensureMotherIsFemale
    // ---------------------------------------------------------------------

    public function testMotherIsNullPasses(): void
    {
        $policy = $this->makePolicy();

        $policy->ensureCanAssignParents(catId: null, motherId: null, fatherIds: []);

        $this->assertTrue(true);
    }

    public function testMotherIsFemalePasses(): void
    {
        $mother = $this->makeCat(id: 100, age: 10, gender: 'female');

        $repo = $this->makeRepository(['100' => $mother]);
        $policy = new CatKinshipPolicy($repo);

        $policy->ensureCanAssignParents(catId: null, motherId: 100, fatherIds: []);

        $this->assertTrue(true);
    }

    public function testMotherNotFoundThrows(): void
    {
        $repo = $this->makeRepository([]);
        $policy = new CatKinshipPolicy($repo);

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: null, motherId: 999, fatherIds: []);
    }

    public function testMotherIsMaleThrows(): void
    {
        $mother = $this->makeCat(id: 100, age: 10, gender: 'male');

        $repo = $this->makeRepository(['100' => $mother]);
        $policy = new CatKinshipPolicy($repo);

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: null, motherId: 100, fatherIds: []);
    }

    // ---------------------------------------------------------------------
    // ensureFathersAreMale
    // ---------------------------------------------------------------------

    public function testNoFathersPasses(): void
    {
        $policy = $this->makePolicy();

        $policy->ensureCanAssignParents(catId: null, motherId: null, fatherIds: []);

        $this->assertTrue(true);
    }

    public function testFathersAreMalePasses(): void
    {
        $father1 = $this->makeCat(id: 200, age: 8, gender: 'male');
        $father2 = $this->makeCat(id: 201, age: 9, gender: 'male');

        $repo = $this->makeRepository(['200' => $father1, '201' => $father2]);
        $policy = new CatKinshipPolicy($repo);

        $policy->ensureCanAssignParents(catId: null, motherId: null, fatherIds: [200, 201]);

        $this->assertTrue(true);
    }

    public function testFatherNotFoundThrows(): void
    {
        $repo = $this->makeRepository([]);
        $policy = new CatKinshipPolicy($repo);

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: null, motherId: null, fatherIds: [999]);
    }

    public function testFatherIsFemaleThrows(): void
    {
        $father = $this->makeCat(id: 200, age: 8, gender: 'female');

        $repo = $this->makeRepository(['200' => $father]);
        $policy = new CatKinshipPolicy($repo);

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: null, motherId: null, fatherIds: [200]);
    }

    // ---------------------------------------------------------------------
    // ensureNoCycle
    // ---------------------------------------------------------------------

    public function testCreateSkipsCycleCheck(): void
    {
        $mother = $this->makeCat(id: 100, age: 10, gender: 'female');
        $father = $this->makeCat(id: 200, age: 8, gender: 'male');

        $repo = $this->makeRepository([
            '100' => $mother,
            '200' => $father,
        ]);
        $policy = new CatKinshipPolicy($repo);

        // catId = null → ensureNoCycle пропускается,
        // а остальные проверки проходят
        $policy->ensureCanAssignParents(catId: null, motherId: 100, fatherIds: [200]);

        $this->assertTrue(true);
    }

    public function testCatIsNotItsOwnAncestorPasses(): void
    {
        $mother = $this->makeCat(id: 100, age: 10, gender: 'female');
        $father = $this->makeCat(id: 200, age: 8, gender: 'male');

        $repo = $this->makeRepository([
            '100' => $mother,
            '200' => $father,
        ]);
        $policy = new CatKinshipPolicy($repo);

        $policy->ensureCanAssignParents(catId: 5, motherId: 100, fatherIds: [200]);

        $this->assertTrue(true);
    }

    public function testMotherIsAncestorThrows(): void
    {
        // Мать (100) — прямой предок кошки (5), значит цикл.
        // Симулируем: у кошки id=5 есть предок id=5? Нет, предок — 5 через мать.
        // Значит нужно, чтобы 5 нашлась в цепочке предков 100.
        // Сделаем: 100 -> mother_id = 5, тогда 5 найдётся в предках.
        $mother = $this->makeCat(id: 100, age: 10, gender: 'female', motherId: 5);

        $repo = $this->makeRepository(['100' => $mother]);
        $policy = new CatKinshipPolicy($repo);

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: 5, motherId: 100, fatherIds: []);
    }

    public function testFatherIsAncestorThrows(): void
    {
        // Отец (200) имеет мать 5 — значит 5 найдётся в предках 200.
        $father = $this->makeCat(id: 200, age: 8, gender: 'male', motherId: 5);

        $repo = $this->makeRepository(['200' => $father]);
        $policy = new CatKinshipPolicy($repo);

        $this->expectException(BadRequestHttpException::class);

        $policy->ensureCanAssignParents(catId: 5, motherId: null, fatherIds: [200]);
    }

    // ---------------------------------------------------------------------
    // Полный happy-path
    // ---------------------------------------------------------------------

    public function testFullValidAssignPasses(): void
    {
        $mother = $this->makeCat(id: 100, age: 10, gender: 'female');
        $father = $this->makeCat(id: 200, age: 8, gender: 'male');

        $repo = $this->makeRepository([
            '100' => $mother,
            '200' => $father,
        ]);
        $policy = new CatKinshipPolicy($repo);

        $policy->ensureCanAssignParents(catId: 5, motherId: 100, fatherIds: [200]);

        $this->assertTrue(true);
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    private function makePolicy(): CatKinshipPolicy
    {
        return new CatKinshipPolicy($this->makeRepository([]));
    }

    /**
     * @param array<string, CatRegistry> $byId
     */
    private function makeRepository(array $byId): CatRegistryRepositoryInterface
    {
        return new class ($byId) implements CatRegistryRepositoryInterface {
            /**
             * @param array<string, CatRegistry> $byId
             */
            public function __construct(private array $byId)
            {
            }

            public function findId(int $id): ?CatRegistry
            {
                return $this->byId[(string) $id] ?? null;
            }

            public function existsId(int $id): bool
            {
                return isset($this->byId[(string) $id]);
            }

            public function findByNameAndGender(int $id, string $name, string $gender): array
            {
                return [];
            }

            public function findAll(\CatRegistry\applications\forms\FilterCatForm $form): array
            {
                return [];
            }

            public function countForFilter(\CatRegistry\applications\forms\FilterCatForm $form): ?int
            {
                return null;
            }

            public function create(CatRegistry $catRegistry): void
            {
            }

            public function update(CatRegistry $catRegistry): void
            {
            }

            public function delete(CatRegistry $catRegistry): void
            {
            }
        };
    }

    private function makeCat(
        int $id,
        int $age,
        string $gender,
        ?int $motherId = null,
        array $fathers = [],
    ): CatRegistry {
        $cat = new CatRegistry();
        $cat->id = $id;
        $cat->age = $age;
        $cat->gender = $gender;
        $cat->mother_id = $motherId;

        if ($fathers !== []) {
            $cat->populateRelation('fathers', $fathers);
        }

        return $cat;
    }
}
