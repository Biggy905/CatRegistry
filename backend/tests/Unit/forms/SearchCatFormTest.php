<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\forms;

use CatRegistry\applications\forms\SearchCatForm;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class SearchCatFormTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // Полностью валидный запрос
    // ---------------------------------------------------------------------

    public function testFullValidSearchPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testSearchWithGenderFemalePasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 100,
            'name' => 'Mila',
            'gender' => 'female',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testSearchWithNameAtLowerBoundPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'A',
            'gender' => 'male',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testSearchWithNameAtUpperBoundPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Abcdefghijklmno',
            'gender' => 'male',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // exclude_cat_id
    // ---------------------------------------------------------------------

    public function testMissingExcludeCatIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('exclude_cat_id', $form->getDataErrors());
    }

    public function testNullExcludeCatIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => null,
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('exclude_cat_id', $form->getDataErrors());
    }

    public function testNonNumericExcludeCatIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 'abc',
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('exclude_cat_id', $form->getDataErrors());
    }

    public function testNumericStringExcludeCatIdIsAccepted(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => '1',
            'name' => 'Barsik',
            'gender' => 'male',
        ]);

        // integer приводится к int(1), required видит непустое значение
        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // name
    // ---------------------------------------------------------------------

    public function testMissingNameReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testNullNameReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => null,
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testEmptyNameReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => '',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testNameAboveUpperBoundReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Abcdefghijklmnop',
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    public function testIntNameReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 123,
            'gender' => 'male',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // gender
    // ---------------------------------------------------------------------

    public function testMissingGenderReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Barsik',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    public function testNullGenderReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Barsik',
            'gender' => null,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    public function testEmptyGenderReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Barsik',
            'gender' => '',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    public function testGenderOutsideEnumReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 1,
            'name' => 'Barsik',
            'gender' => 'other',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Комбинации
    // ---------------------------------------------------------------------

    public function testEmptyRequestReturnsErrorsForAllRequired(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([]);

        $this->assertFalse($result);

        $errors = $form->getDataErrors();
        $this->assertArrayHasKey('exclude_cat_id', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('gender', $errors);
    }

    public function testAllFieldsInvalidReturnsErrorsForAll(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'exclude_cat_id' => 'abc',
            'name' => '',
            'gender' => 'other',
        ]);

        $this->assertFalse($result);

        $errors = $form->getDataErrors();
        $this->assertArrayHasKey('exclude_cat_id', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('gender', $errors);
    }

    private function makeForm(): SearchCatForm
    {
        return new SearchCatForm();
    }
}
