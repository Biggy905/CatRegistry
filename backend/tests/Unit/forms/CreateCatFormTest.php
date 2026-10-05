<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\forms;

use CatRegistry\applications\forms\CreateCatForm;
use CatRegistry\applications\repositories\databases\CatRegistryRepository;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;
use yii\web\NotFoundHttpException;

final class CreateCatFormTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // Позитивные сценарии: валидный int-ввод
    // ---------------------------------------------------------------------

    public function testOrphanWithoutParentsPassesValidation(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'NewOrphan',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testKittenWithExistingMotherPassesValidation(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'WithMother',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 100,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testKittenWithMotherAndOneExistingFatherPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'OneFather',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 100,
            'father_ids' => [200],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testKittenWithMotherAndThreeExistingFathersPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'ThreeFathers',
            'gender' => 'male',
            'age' => 2,
            'mother_id' => 101,
            'father_ids' => [200, 201, 202],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testKittenWithoutMotherAndWithOneFatherPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'OnlyFather',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [203],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testKittenWithoutMotherAndWithThreeFathersPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
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
            'name' => 'Abcdefghijklmnop',
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Строковый ввод mother_id / father_ids — табу
    // ---------------------------------------------------------------------

    public function testStringMotherIdIsRejected(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
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
            'name' => 'Kitten',
            'gender' => 'male',
            'age' => 2,
            'father_ids' => [200, 'abc'],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('father_ids', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Несуществующие id — исключение
    // ---------------------------------------------------------------------

    public function testNonExistentMotherThrowsNotFound(): void
    {
        $form = $this->makeForm();

        $this->expectException(NotFoundHttpException::class);

        $form->runValidate([
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
            'gender' => 'male',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testAgeBelowOneReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
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
            'name' => 'Barsik',
            'gender' => 'other',
            'age' => 2,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    public function testMissingGenderReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
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
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    private function makeForm(): CreateCatForm
    {
        return new CreateCatForm(
            new CatRegistryRepository()
        );
    }
}
