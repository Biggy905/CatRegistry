<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\enums;

use CatRegistry\applications\enums\CatGenderEnums;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;

final class CatGenderEnumsTest extends Unit
{
    protected UnitTester $tester;

    public function testMaleValue(): void
    {
        $this->assertSame('male', CatGenderEnums::CAT_GENDER_MALE->value);
    }

    public function testFemaleValue(): void
    {
        $this->assertSame('female', CatGenderEnums::CAT_GENDER_FEMALE->value);
    }

    public function testToArrayReturnsAllValues(): void
    {
        $this->assertSame(
            ['male', 'female'],
            CatGenderEnums::toArray(),
        );
    }

    public function testToArrayReturnsList(): void
    {
        $this->assertSame(
            [0, 1],
            array_keys(CatGenderEnums::toArray()),
        );
    }

    public function testCasesCountMatchesToArrayCount(): void
    {
        $this->assertCount(
            count(CatGenderEnums::cases()),
            CatGenderEnums::toArray(),
        );
    }
}
