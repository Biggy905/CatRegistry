<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\forms;

use CatRegistry\applications\forms\IdCatForm;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class IdCatFormTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // id — целое число
    // ---------------------------------------------------------------------

    public function testPositiveIntIdPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testLargeIntIdPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 999999,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    public function testNegativeIntIdPasses(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => -1,
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // id — отсутствие / пустота
    // ---------------------------------------------------------------------

    public function testMissingIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    public function testNullIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => null,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    public function testEmptyStringIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => '',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // id — невалидные значения
    // ---------------------------------------------------------------------

    public function testNonNumericStringIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 'abc',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    public function testFloatIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => 1.5,
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    public function testArrayIdReturnsError(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => [1],
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('id', $form->getDataErrors());
    }

    // ---------------------------------------------------------------------
    // Поведение string-id: зависит от того, какое правило сработает первым
    // ---------------------------------------------------------------------

    public function testNumericStringIdBehaviour(): void
    {
        $form = $this->makeForm();

        $result = $form->runValidate([
            'id' => '1',
        ]);

        $this->assertTrue($result);
        $this->assertSame([], $form->getDataErrors());
    }

    private function makeForm(): IdCatForm
    {
        return new IdCatForm();
    }
}
