<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\repositories;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\repositories\databases\CatRegistryRepository;
use CatRegistry\tests\Support\UnitTester;
use CatRegistry\tests\Unit\fixtures\CatMaleFixture;
use CatRegistry\tests\Unit\fixtures\CatRegistryFixture;
use Codeception\Test\Unit;
use LogicException;

final class CatRegistryRepositoryTest extends Unit
{
    protected UnitTester $tester;

    public function _fixtures(): array
    {
        return [
            'catRegistry' => CatRegistryFixture::class,
            'catMale' => CatMaleFixture::class,
        ];
    }

    // ---------------------------------------------------------------------
    // findId
    // ---------------------------------------------------------------------

    public function testFindIdReturnsExistingCat(): void
    {
        $repo = $this->makeRepo();

        $cat = $repo->findId(100);

        $this->assertInstanceOf(CatRegistry::class, $cat);
        $this->assertSame(100, $cat->id);
        $this->assertSame('Mother A', $cat->name);
    }

    public function testFindIdReturnsNullForNonExistent(): void
    {
        $repo = $this->makeRepo();

        $this->assertNull($repo->findId(99999));
    }

    public function testFindIdExcludesSoftDeleted(): void
    {
        // помечаем запись как удалённую
        $cat = CatRegistry::findOne(100);
        $this->assertNotNull($cat);
        $cat->deleted_at = '2025-01-01 00:00:00';
        $cat->save(false);

        $repo = $this->makeRepo();

        $this->assertNull($repo->findId(100));
    }

    // ---------------------------------------------------------------------
    // existsId
    // ---------------------------------------------------------------------

    public function testExistsIdReturnsTrueForExisting(): void
    {
        $repo = $this->makeRepo();

        $this->assertTrue($repo->existsId(100));
    }

    public function testExistsIdReturnsFalseForNonExistent(): void
    {
        $repo = $this->makeRepo();

        $this->assertFalse($repo->existsId(99999));
    }

    public function testExistsIdReturnsFalseForSoftDeleted(): void
    {
        $cat = CatRegistry::findOne(100);
        $this->assertNotNull($cat);
        $cat->deleted_at = '2025-01-01 00:00:00';
        $cat->save(false);

        $repo = $this->makeRepo();

        $this->assertFalse($repo->existsId(100));
    }

    // ---------------------------------------------------------------------
    // create / update / delete
    // ---------------------------------------------------------------------

    public function testCreateStoresNewCat(): void
    {
        $repo = $this->makeRepo();

        $cat = new CatRegistry();
        $cat->name = 'NewCat';
        $cat->age = 3;
        $cat->gender = 'male';
        $cat->created_at = '2025-01-01 00:00:00';

        $repo->create($cat);

        $this->assertNotNull($cat->id);
        $this->assertSame('NewCat', CatRegistry::findOne($cat->id)->name);
    }

    public function testUpdateStoresChanges(): void
    {
        $repo = $this->makeRepo();

        $cat = $repo->findId(100);
        $this->assertNotNull($cat);
        $cat->name = 'Renamed';

        $repo->update($cat);

        $this->assertSame('Renamed', $repo->findId(100)->name);
    }

    public function testDeleteSoftDeletesCat(): void
    {
        $repo = $this->makeRepo();

        $cat = $repo->findId(100);
        $this->assertNotNull($cat);

        $repo->delete($cat);

        // через обычный find уже не найдётся
        $this->assertNull($repo->findId(100));

        // но запись в БД осталась с deleted_at
        $raw = CatRegistry::find()
            ->andWhere(['id' => 100])
            ->one();
        // из-за override find() deleted_at != null — используем прямой SQL
        $row = \Yii::$app->db
            ->createCommand('SELECT deleted_at FROM cat_registries WHERE id = :id', [':id' => 100])
            ->queryScalar();

        $this->assertNotNull($row);
    }

    public function testCreateThrowsOnDbConstraintViolation(): void
    {
        $repo = $this->makeRepo();

        $cat = new CatRegistry();
        $cat->name = null;
        $cat->age = null;
        $cat->gender = null;

        $this->expectException(\yii\db\IntegrityException::class);

        $repo->create($cat);
    }

    // ---------------------------------------------------------------------
    // findAll с фильтром
    // ---------------------------------------------------------------------

    public function testFindAllWithoutFiltersReturnsAll(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();

        $cats = $repo->findAll($form);

        // в фикстуре 16 записей (3 female + 4 male + 6 детей + 3 дополнительные)
        $this->assertNotEmpty($cats);
        $this->assertContainsOnlyInstancesOf(CatRegistry::class, $cats);
    }

    public function testFindAllFiltersByGender(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->gender = 'female';

        $cats = $repo->findAll($form);

        foreach ($cats as $cat) {
            $this->assertSame('female', $cat->gender);
        }
    }

    public function testFindAllFiltersByAgeRange(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->age = [5, 11];

        $cats = $repo->findAll($form);

        foreach ($cats as $cat) {
            $this->assertGreaterThanOrEqual(5, $cat->age);
            $this->assertLessThanOrEqual(11, $cat->age);
        }
    }

    public function testFindAllSwapsAgeRangeWhenMinGreaterThanMax(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->age = [11, 5];

        $cats = $repo->findAll($form);

        foreach ($cats as $cat) {
            $this->assertGreaterThanOrEqual(5, $cat->age);
            $this->assertLessThanOrEqual(11, $cat->age);
        }
    }

    public function testFindAllOrdersByName(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();

        $cats = $repo->findAll($form);

        $names = array_map(static fn(CatRegistry $c) => $c->name, $cats);
        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, $names);
    }

    public function testFindAllAppliesLimitAndOffset(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->limit = 10;
        $form->page = 1;

        $page1 = $repo->findAll($form);

        $form->page = 2;
        $page2 = $repo->findAll($form);

        $this->assertCount(10, $page1);
        // вторая страница может быть меньше, если записей < 20
        $this->assertLessThanOrEqual(10, count($page2));

        $ids1 = array_map(static fn(CatRegistry $c) => $c->id, $page1);
        $ids2 = array_map(static fn(CatRegistry $c) => $c->id, $page2);

        $this->assertEmpty(array_intersect($ids1, $ids2));
    }

    public function testFindAllFallsBackToDefaultLimitWhenOutOfRange(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->limit = 5;    // меньше 10 → сброс на 10

        $cats = $repo->findAll($form);

        $this->assertLessThanOrEqual(10, count($cats));
        $this->assertSame(10, $form->limit);
    }

    // ---------------------------------------------------------------------
    // countForFilter
    // ---------------------------------------------------------------------

    public function testCountForFilterReturnsNullForNoResults(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->gender = 'unknown';

        $count = $repo->countForFilter($form);

        $this->assertNull($count);
    }

    public function testCountForFilterReturnsCountWithFilters(): void
    {
        $repo = $this->makeRepo();
        $form = new FilterCatForm();
        $form->gender = 'female';

        $count = $repo->countForFilter($form);

        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    // ---------------------------------------------------------------------
    // findByNameAndGender
    // ---------------------------------------------------------------------

    public function testFindByNameAndGenderExcludesId(): void
    {
        $repo = $this->makeRepo();

        $cats = $repo->findByNameAndGender(id: 100, name: '', gender: '');

        foreach ($cats as $cat) {
            $this->assertNotSame(100, $cat->id);
        }
    }

    public function testFindByNameAndGenderFiltersByName(): void
    {
        $repo = $this->makeRepo();

        $cats = $repo->findByNameAndGender(id: 0, name: 'Mother', gender: '');

        foreach ($cats as $cat) {
            $this->assertStringContainsStringIgnoringCase('Mother', $cat->name);
        }
    }

    public function testFindByNameAndGenderFiltersByGender(): void
    {
        $repo = $this->makeRepo();

        $cats = $repo->findByNameAndGender(id: 0, name: '', gender: 'female');

        foreach ($cats as $cat) {
            $this->assertSame('female', $cat->gender);
        }
    }

    public function testFindByNameAndGenderLimitsTo50(): void
    {
        $repo = $this->makeRepo();

        $cats = $repo->findByNameAndGender(id: 0, name: '', gender: '');

        $this->assertLessThanOrEqual(50, count($cats));
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    private function makeRepo(): CatRegistryRepository
    {
        return new CatRegistryRepository();
    }
}
