<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\forms;

use CatRegistry\applications\exceptions\BadRequestHttpException;
use CatRegistry\applications\forms\UpdateCatForm;
use CatRegistry\applications\repositories\databases\CatRegistryRepository;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;
use yii\web\NotFoundHttpException;

final class UpdateCatFormTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // Позитивные сценарии: валидный int-ввод
    // ---------------------------------------------------------------------

    public function testUpdateOrphanWithoutParentsPassesValidation(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'NewOrphan',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testUpdateKittenWithExistingMotherPassesValidation(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'WithMother',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 100,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testUpdateKittenWithMotherAndOneExistingFatherPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'OneFather',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 100,
            'father_ids' => [200],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testUpdateKittenWithMotherAndThreeExistingFathersPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'ThreeFathers',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 101,
            'father_ids' => [200, 201, 202],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testUpdateKittenWithoutMotherAndWithOneFatherPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'OnlyFather',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [203],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testUpdateKittenWithoutMotherAndWithThreeFathersPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'ThreeFathNoM',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [200, 201, 203],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Границы длины имени: 3..15
    // ---------------------------------------------------------------------

    public function testNameAtLowerBoundPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Aba',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testNameAtUpperBoundPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Abcdefghijklmno',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testNameBelowLowerBoundReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Ba',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testNameAboveUpperBoundReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Abcdefghijklmnop',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // id
    // ---------------------------------------------------------------------

    public function testMissingIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'Barsik',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    public function testStringIdIsRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => '1',
            'name' => 'Barsik',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    public function testNonExistentIdThrowsNotFound(): void
    {
        $form = $this->makeForm();

        $this->expectException(NotFoundHttpException::class);

        $form->runValidate([
            'id' => 9999,
            'name' => 'Barsik',
            'gender' => 'male',
            'age' => 2,
        ]);
    }

    // ---------------------------------------------------------------------
    // mother_id / father_ids — строки табу
    // ---------------------------------------------------------------------

    public function testStringMotherIdIsRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => '100',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('mother_id', $form->getDataErrors());
    }

    public function testNonNumericMotherIdIsRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 'abc',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('mother_id', $form->getDataErrors());
    }

    public function testStringFatherIdIsRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => ['200'],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('father_ids', $form->getDataErrors());
    }

    public function testMixedIntAndStringFatherIdsAreRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [200, '201'],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('father_ids', $form->getDataErrors());
    }

    public function testNonNumericFatherIdIsRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [200, 'abc'],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('father_ids', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Несуществующие mother_id / father_ids — исключение
    // ---------------------------------------------------------------------

    public function testNonExistentMotherThrowsNotFound(): void
    {
        $form = $this->makeForm();

        $this->expectException(NotFoundHttpException::class);

        $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 9999,
        ]);
    }

    public function testNonExistentFatherThrowsNotFound(): void
    {
        $form = $this->makeForm();

        $this->expectException(NotFoundHttpException::class);

        $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [200, 9999],
        ]);
    }

    // ---------------------------------------------------------------------
    // Возраст матери
    // ---------------------------------------------------------------------

    public function testYoungerMotherAddsAgeError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 20,
            'mother_id' => 100,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Общие правила Yii
    // ---------------------------------------------------------------------

    public function testMissingNameReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testMissingGenderReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Barsik',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    public function testMissingAgeReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testAgeBelowOneReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Barsik',
            'gender' => 'male',
            'age' => 0,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testAgeAboveThirtyReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Barsik',
            'gender' => 'male',
            'age' => 31,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testGenderOutsideEnumReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
            'name' => 'Barsik',
            'gender' => 'other',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    private function makeForm(): UpdateCatForm
    {
        return new UpdateCatForm(
            new CatRegistryRepository()
        );
    }
}
