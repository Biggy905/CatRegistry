<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\forms;

use CatRegistry\applications\enums\CatGenderEnums;
use CatRegistry\applications\forms\FilterCatForm;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class FilterCatFormTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // Полностью пустой запрос — всё валидно
    // ---------------------------------------------------------------------

    public function testEmptyFilterPassesValidation(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // page / limit — правила integer
    // ---------------------------------------------------------------------

    public function testPageAsIntPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'page' => 2,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testLimitAsIntPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'limit' => 20,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testPageAndLimitAsIntPass(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'page' => 3,
            'limit' => 20,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testNonNumericPageReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'page' => 'abc',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('page', $form->getDataErrors());
    }

    public function testNonNumericLimitReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'limit' => 'abc',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('limit', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // age — кастомный validateAge
    // ---------------------------------------------------------------------

    public function testAgeNotProvidedPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([]);

        $this->assertTrue($result);
    }

    public function testAgeAsEmptyStringPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => '',
        ]);

        $this->assertTrue($result);
    }

    public function testAgeAsArrayOfTwoIntsPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => [1, 5],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testAgeAsArrayOfTwoNumericStringsPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => ['1', '5'],
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testAgeAsNonArrayReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => 5,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testAgeAsArrayWithOneElementReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => [1],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testAgeAsArrayWithThreeElementsReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => [1, 5, 10],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testAgeAsArrayWithNonNumericElementReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => [1, 'abc'],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    public function testAgeAsArrayWithFloatElementReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'age' => [1, '1.5'],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('age', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // gender — in
    // ---------------------------------------------------------------------

    public function testGenderNotProvidedPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([]);

        $this->assertTrue($result);
    }

    public function testGenderMalePasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'gender' => 'male',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testGenderFemalePasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'gender' => 'female',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testGenderOutsideEnumReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'gender' => 'other',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('gender', $form->getDataErrors());
    }

    public function testGenderWithSurroundingWhitespaceIsTrimmedAndPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'gender' => '  male  ',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Комбинации
    // ---------------------------------------------------------------------

    public function testFullValidFilterPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'page' => 2,
            'limit' => 20,
            'age' => [1, 5],
            'gender' => 'male',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testAllInvalidFieldsReturnErrors(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'page' => 'abc',
            'limit' => 'def',
            'age' => [1],
            'gender' => 'other',
        ]);

        $this->assertFalse($result);

        $errors = $form->getDataErrors();
        $this->assertArrayHasKey('page', $errors);
        $this->assertArrayHasKey('limit', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertArrayHasKey('gender', $errors);
    }

    private function makeForm(): FilterCatForm
    {
        return new FilterCatForm();
    }
}
