<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\components;

use CatRegistry\applications\components\ErrorHandler;
use CatRegistry\applications\exceptions\BadRequestHttpException;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;
use DomainException;
use LogicException;
use RuntimeException;
use yii\base\ErrorException;
use yii\base\Exception as YiiBaseException;
use yii\db\Exception as YiiDbException;
use yii\web\HttpException;

final class ErrorHandlerTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // BadRequestHttpException
    // ---------------------------------------------------------------------

    public function testBadRequestProducesJsonWithErrors(): void
    {
        $data = $this->renderJson(new BadRequestHttpException([
            'mother_id' => ['Кошка не может быть своей матерью.'],
        ]));

        $this->assertSame(400, $data['code']);
        $this->assertSame(
            ['mother_id' => ['Кошка не может быть своей матерью.']],
            $data['errors'],
        );
        $this->assertArrayHasKey('name', $data);
    }

    public function testBadRequestAlwaysProduces400(): void
    {
        $data = $this->renderJson(new BadRequestHttpException(
            data: ['id' => ['Некорректный id']],
            message: 'Ошибка',
        ));

        $this->assertSame(400, $data['code']);
    }

    // ---------------------------------------------------------------------
    // DomainException
    // ---------------------------------------------------------------------

    public function testDomainExceptionWithCustomCodeUsesIt(): void
    {
        $data = $this->renderJson(new DomainException('Невалидное состояние', 409));

        $this->assertSame(409, $data['code']);
        $this->assertSame('Невалидное состояние', $data['message']);
    }

    public function testDomainExceptionWithZeroCodeFallsBackTo500(): void
    {
        $data = $this->renderJson(new DomainException('Домен сломался'));

        $this->assertSame(500, $data['code']);
        $this->assertSame('Домен сломался', $data['message']);
    }

    // ---------------------------------------------------------------------
    // LogicException
    // ---------------------------------------------------------------------

    public function testLogicExceptionWithCustomCodeUsesIt(): void
    {
        $data = $this->renderJson(new LogicException('Логика сломалась', 422));

        $this->assertSame(422, $data['code']);
        $this->assertSame('Логика сломалась', $data['message']);
    }

    public function testLogicExceptionWithZeroCodeFallsBackTo500(): void
    {
        $data = $this->renderJson(new LogicException('Логика сломалась'));

        $this->assertSame(500, $data['code']);
    }

    // ---------------------------------------------------------------------
    // HttpException — ветка else
    // ---------------------------------------------------------------------

    public function testHttpExceptionProducesArrayMessage(): void
    {
        $data = $this->renderJson(new HttpException(404, 'Не найдено'));

        $this->assertIsArray($data['message']);
        $this->assertSame('Не найдено', $data['message']['message']);
    }

    // ---------------------------------------------------------------------
    // Generic
    // ---------------------------------------------------------------------

    public function testGenericExceptionProduces500(): void
    {
        $data = $this->renderJson(new RuntimeException('Что-то упало'));

        $this->assertSame(500, $data['code']);
        $this->assertIsArray($data['message']);
        $this->assertSame('Что-то упало', $data['message']['message']);
    }

    // ---------------------------------------------------------------------
    // convertExceptionToArray
    // ---------------------------------------------------------------------

    public function testConvertExceptionForGenericExceptionHasFileAndLine(): void
    {
        $array = $this->invokeConvert(new RuntimeException('boom'));

        $this->assertSame('Exception', $array['name']);
        $this->assertSame('boom', $array['message']);
        $this->assertSame(0, $array['code']);
        $this->assertSame(RuntimeException::class, $array['type']);
        $this->assertArrayHasKey('file', $array);
        $this->assertArrayHasKey('line', $array);
        $this->assertArrayHasKey('stack-trace', $array);
    }

    public function testConvertExceptionForYiiBaseExceptionKeepsType(): void
    {
        $array = $this->invokeConvert(new YiiBaseException('yii error'));

        $this->assertSame('yii error', $array['message']);
        $this->assertSame(YiiBaseException::class, $array['type']);
        $this->assertArrayHasKey('name', $array);
    }

    public function testConvertExceptionForUserExceptionOmitsFileAndLine(): void
    {
        $array = $this->invokeConvert(new HttpException(400, 'user error'));

        $this->assertArrayNotHasKey('file', $array);
        $this->assertArrayNotHasKey('line', $array);
        $this->assertArrayNotHasKey('stack-trace', $array);
    }

    public function testConvertExceptionIncludesPreviousException(): void
    {
        $previous = new RuntimeException('root cause');
        $exception = new RuntimeException('outer', 0, $previous);

        $array = $this->invokeConvert($exception);

        $this->assertArrayHasKey('previous', $array);
        $this->assertSame('root cause', $array['previous']['message']);
    }

    public function testConvertExceptionForDbExceptionAddsErrorInfo(): void
    {
        $array = $this->invokeConvert(new YiiDbException('sql error'));

        $this->assertArrayHasKey('error-info', $array);
    }

    public function testConvertExceptionForYiiErrorException(): void
    {
        $array = $this->invokeConvert(new ErrorException('php error'));

        $this->assertSame('php error', $array['message']);
        $this->assertArrayHasKey('file', $array);
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function renderJson(\Throwable $exception): array
    {
        /** @var ErrorHandler $handler */
        $handler = \Yii::$app->getErrorHandler();

        $method = new \ReflectionMethod($handler, 'renderException');
        $method->setAccessible(true);

        ob_start();
        try {
            $method->invoke($handler, $exception);
        } finally {
            $output = (string) ob_get_clean();
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function invokeConvert(\Throwable $exception): array
    {
        /** @var ErrorHandler $handler */
        $handler = \Yii::$app->getErrorHandler();

        $method = new \ReflectionMethod($handler, 'convertExceptionToArray');
        $method->setAccessible(true);

        /** @var array<string, mixed> $result */
        $result = $method->invoke($handler, $exception);

        return $result;
    }
}
