<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\services;

use CatRegistry\applications\entities\CatRegistry;
use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\applications\forms\SearchCatForm;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\policies\CatKinshipPolicy;
use CatRegistry\applications\repositories\databases\CatRegistryRepository;
use CatRegistry\applications\services\CatRegistryServices;
use CatRegistry\tests\Support\UnitTester;
use CatRegistry\tests\Unit\fixtures\CatMaleFixture;
use CatRegistry\tests\Unit\fixtures\CatRegistryFixture;
use Codeception\Test\Unit;
use yii\web\NotFoundHttpException;

final class CatRegistryServicesTest extends Unit
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
    // item
    // ---------------------------------------------------------------------

    public function testItemReturnsCatData(): void
    {
        $service = $this->makeService();

        $form = new IdCatForm();
        $form->id = 1;

        $data = $service->item($form);

        $this->assertSame(1, $data['id']);
        $this->assertSame('Orphan', $data['name']);
        $this->assertSame(2, $data['age']);
        $this->assertSame('male', $data['gender']);
        $this->assertArrayHasKey('mother', $data);
        $this->assertArrayHasKey('fathers', $data);
    }

    public function testItemWithMotherIncludesMother(): void
    {
        $service = $this->makeService();

        $form = new IdCatForm();
        $form->id = 2;   // WithMother → mother_id = 100

        $data = $service->item($form);

        $this->assertIsArray($data['mother']);
        $this->assertSame(100, $data['mother']['id']);
        $this->assertSame('Mother A', $data['mother']['name']);
    }

    public function testItemWithFathersIncludesThem(): void
    {
        $service = $this->makeService();

        $form = new IdCatForm();
        $form->id = 4;   // ThreeFathers → fathers 200, 201, 202

        $data = $service->item($form);

        $this->assertIsArray($data['fathers']);
        $this->assertCount(3, $data['fathers']);
    }

    public function testItemThrowsWhenNotFound(): void
    {
        $service = $this->makeService();

        $form = new IdCatForm();
        $form->id = 99999;

        $this->expectException(NotFoundHttpException::class);

        $service->item($form);
    }

    // ---------------------------------------------------------------------
    // list
    // ---------------------------------------------------------------------

    public function testListReturnsItemsAndPagination(): void
    {
        $service = $this->makeService();

        $form = new FilterCatForm();
        $form->page = 1;
        $form->limit = 10;

        $data = $service->list($form);

        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('items', $data);

        $this->assertIsArray($data['items']);
        $this->assertSame(1, $data['page']);
        $this->assertSame(10, $data['limit']);
    }

    public function testListTotalIsInt(): void
    {
        $service = $this->makeService();

        $form = new FilterCatForm();
        $form->gender = 'female';

        $data = $service->list($form);

        $this->assertIsInt($data['total']);
        $this->assertGreaterThan(0, $data['total']);
    }

    public function testListWithUnknownGenderReturnsZeroTotal(): void
    {
        $service = $this->makeService();

        $form = new FilterCatForm();
        $form->gender = 'unknown';

        $data = $service->list($form);

        $this->assertSame(0, $data['total']);
        $this->assertSame([], $data['items']);
    }

    // ---------------------------------------------------------------------
    // search
    // ---------------------------------------------------------------------

    public function testSearchReturnsItems(): void
    {
        $service = $this->makeService();

        $form = new SearchCatForm();
        $form->exclude_cat_id = 0;
        $form->name = '';
        $form->gender = '';

        $data = $service->search($form);

        $this->assertArrayHasKey('items', $data);
        $this->assertIsArray($data['items']);
    }

    public function testSearchExcludesCatId(): void
    {
        $service = $this->makeService();

        $form = new SearchCatForm();
        $form->exclude_cat_id = 100;
        $form->name = '';
        $form->gender = '';

        $data = $service->search($form);

        $ids = array_map(static fn(array $item) => $item['id'], $data['items']);
        $this->assertNotContains(100, $ids);
    }

    public function testSearchFiltersByGender(): void
    {
        $service = $this->makeService();

        $form = new SearchCatForm();
        $form->exclude_cat_id = 0;
        $form->name = '';
        $form->gender = 'female';

        $data = $service->search($form);

        foreach ($data['items'] as $item) {
            $this->assertSame('female', $item['gender']);
        }
    }

    // ---------------------------------------------------------------------
    // insert
    // ---------------------------------------------------------------------

    public function testInsertCreatesCat(): void
    {
        $service = $this->makeService();

        $form = new CreateCatForm($this->makeRepo());
        $form->name = 'NewCat';
        $form->age = 3;
        $form->gender = 'male';

        $result = $service->insert($form);

        $this->assertInstanceOf(\CatRegistry\applications\groups\CatSelectItemGroup::class, $result);

        $data = $result->toArray();
        $this->assertSame('NewCat', $data['name']);
        $this->assertSame(3, $data['age']);
        $this->assertSame('male', $data['gender']);
        $this->assertNotNull($data['id']);
    }

    public function testInsertWithMotherAndFathersLinksThem(): void
    {
        $service = $this->makeService();

        $form = new CreateCatForm($this->makeRepo());
        $form->name = 'WithParents';
        $form->age = 2;
        $form->gender = 'male';
        $form->mother_id = 100;
        $form->father_ids = [200, 201];

        $data = $service->insert($form)->toArray();

        $this->assertIsArray($data['mother']);
        $this->assertSame(100, $data['mother']['id']);

        $this->assertIsArray($data['fathers']);
        $this->assertCount(2, $data['fathers']);
    }

    public function testInsertRollsBackOnPolicyFailure(): void
    {
        $service = $this->makeService();

        $form = new CreateCatForm($this->makeRepo());
        $form->name = 'BadCat';
        $form->age = 3;
        $form->gender = 'male';
        $form->mother_id = 200;   // 200 — male, ensureMotherIsFemale упадёт

        $this->expectException(\CatRegistry\applications\exceptions\BadRequestHttpException::class);

        $service->insert($form);
    }

    // ---------------------------------------------------------------------
    // update
    // ---------------------------------------------------------------------

    public function testUpdateChangesCat(): void
    {
        $service = $this->makeService();

        $form = new UpdateCatForm($this->makeRepo());
        $form->id = 1;
        $form->name = 'Renamed';
        $form->age = 3;
        $form->gender = 'male';

        $data = $service->update($form)->toArray();

        $this->assertSame(1, $data['id']);
        $this->assertSame('Renamed', $data['name']);
        $this->assertSame(3, $data['age']);
    }

    public function testUpdateLinksNewFathers(): void
    {
        $service = $this->makeService();

        $form = new UpdateCatForm($this->makeRepo());
        $form->id = 1;
        $form->name = 'Orphan';
        $form->age = 2;
        $form->gender = 'male';
        $form->father_ids = [200, 201];

        $data = $service->update($form)->toArray();

        $this->assertIsArray($data['fathers']);
        $this->assertCount(2, $data['fathers']);
    }

    public function testUpdateThrowsWhenCatNotFound(): void
    {
        $service = $this->makeService();

        $form = new UpdateCatForm($this->makeRepo());
        $form->id = 99999;
        $form->name = 'Ghost';
        $form->age = 3;
        $form->gender = 'male';

        $this->expectException(NotFoundHttpException::class);

        $service->update($form);
    }

    // ---------------------------------------------------------------------
    // delete
    // ---------------------------------------------------------------------

    public function testDeleteSoftDeletesCat(): void
    {
        $service = $this->makeService();

        $form = new IdCatForm();
        $form->id = 1;

        $service->delete($form);

        $repo = $this->makeRepo();
        $this->assertNull($repo->findId(1));
    }

    public function testDeleteThrowsWhenNotFound(): void
    {
        $service = $this->makeService();

        $form = new IdCatForm();
        $form->id = 99999;

        $this->expectException(NotFoundHttpException::class);

        $service->delete($form);
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    private function makeService(): CatRegistryServices
    {
        $repo = $this->makeRepo();
        $policy = new CatKinshipPolicy($repo);

        return new CatRegistryServices($repo, $policy);
    }

    private function makeRepo(): CatRegistryRepository
    {
        return new CatRegistryRepository();
    }
}
