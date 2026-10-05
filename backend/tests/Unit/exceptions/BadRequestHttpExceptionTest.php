<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\exceptions;

use CatRegistry\applications\exceptions\BadRequestHttpException;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;
use RuntimeException;

final class BadRequestHttpExceptionTest extends Unit
{
    protected UnitTester $tester;

    public function testDefaultDataIsEmpty(): void
    {
        $exception = new BadRequestHttpException();

        $this->assertSame([], $exception->getData());
    }

    public function testDataIsStoredAndReturned(): void
    {
        $data = ['mother_id' => 'Кошка не может быть своей матерью.'];

        $exception = new BadRequestHttpException($data);

        $this->assertSame($data, $exception->getData());
    }

    public function testDefaultMessage(): void
    {
        $exception = new BadRequestHttpException();

        $this->assertSame('Ошибка валидации', $exception->getMessage());
    }

    public function testCustomMessageIsStored(): void
    {
        $exception = new BadRequestHttpException([], 'Своё сообщение');

        $this->assertSame('Своё сообщение', $exception->getMessage());
    }

    public function testStatusCodeIs400(): void
    {
        $exception = new BadRequestHttpException();

        $this->assertSame(400, $exception->statusCode);
    }

    public function testGetCodeReturnsZeroByDefault(): void
    {
        $exception = new BadRequestHttpException();

        $this->assertSame(0, $exception->getCode());
    }

    public function testCustomCodeIsStored(): void
    {
        $exception = new BadRequestHttpException([], 'Ошибка', 422);

        $this->assertSame(422, $exception->getCode());
    }

    public function testPreviousExceptionIsStored(): void
    {
        $previous = new RuntimeException('root cause');
        $exception = new BadRequestHttpException([], 'Ошибка', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testGetNameReturnsBadRequest(): void
    {
        $exception = new BadRequestHttpException();

        $this->assertSame('Bad Request', $exception->getName());
    }
}
