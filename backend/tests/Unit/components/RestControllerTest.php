<?php

declare(strict_types=1);

namespace CatRegistry\tests\Unit\components;

use CatRegistry\applications\components\RestController;
use CatRegistry\tests\Support\UnitTester;
use Codeception\Test\Unit;
use yii\web\Response;

final class RestControllerTest extends Unit
{
    protected UnitTester $tester;

    // ---------------------------------------------------------------------
    // responseOK
    // ---------------------------------------------------------------------

    public function testResponseOkReturnsSuccessStructure(): void
    {
        $controller = $this->makeController();
        $controller->response = new Response();

        $result = $controller->exposeResponseOK(['foo' => 'bar']);

        $this->assertSame(200, $result['code']);
        $this->assertSame(['foo' => 'bar'], $result['data']);
    }

    public function testResponseOkWithoutDataReturnsEmptyArray(): void
    {
        $controller = $this->makeController();
        $controller->response = new Response();

        $result = $controller->exposeResponseOK();

        $this->assertSame(200, $result['code']);
        $this->assertSame([], $result['data']);
    }

    public function testResponseOkSetsHttpStatusAndFormat(): void
    {
        $controller = $this->makeController();
        $controller->response = new Response();

        $controller->exposeResponseOK();

        $this->assertSame(200, $controller->response->statusCode);
        $this->assertSame(Response::FORMAT_JSON, $controller->response->format);
    }

    // ---------------------------------------------------------------------
    // responseCreated
    // ---------------------------------------------------------------------

    public function testResponseCreatedReturnsStructure(): void
    {
        $controller = $this->makeController();
        $controller->response = new Response();

        $result = $controller->exposeResponseCreated(['id' => 1], '/cats/1');

        $this->assertSame(201, $result['code']);
        $this->assertSame(['id' => 1], $result['data']);
    }

    public function testResponseCreatedSetsStatusCodeAndLocation(): void
    {
        $controller = $this->makeController();
        $controller->response = new Response();

        $controller->exposeResponseCreated(['id' => 1], '/cats/1');

        $this->assertSame(201, $controller->response->statusCode);
        $this->assertSame(Response::FORMAT_JSON, $controller->response->format);
        $this->assertSame('/cats/1', $controller->response->headers->get('Location'));
    }

    // ---------------------------------------------------------------------
    // responseNoContent
    // ---------------------------------------------------------------------

    public function testResponseNoContentSets204(): void
    {
        $controller = $this->makeController();
        $controller->response = new Response();

        $controller->exposeResponseNoContent();

        $this->assertSame(204, $controller->response->statusCode);
        $this->assertSame(Response::FORMAT_JSON, $controller->response->format);
    }

    // ---------------------------------------------------------------------
    // getPayload
    // ---------------------------------------------------------------------

    public function testGetPayloadReturnsDecodedJson(): void
    {
        $payload = ['name' => 'Barsik', 'age' => 3];

        $controller = $this->makeController($payload);

        $this->assertSame($payload, $controller->exposeGetPayload());
    }

    public function testGetPayloadReturnsEmptyArrayForInvalidJson(): void
    {
        $controller = $this->makeController(null, rawBody: 'not-json');

        $this->assertSame([], $controller->exposeGetPayload());
    }

    public function testGetPayloadReturnsEmptyArrayForEmptyBody(): void
    {
        $controller = $this->makeController(null, rawBody: '');

        $this->assertSame([], $controller->exposeGetPayload());
    }

    public function testGetPayloadReturnsEmptyArrayForNullJson(): void
    {
        $controller = $this->makeController(null, rawBody: 'null');

        $this->assertSame([], $controller->exposeGetPayload());
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    /**
     * @param array<string, mixed>|null $payload
     */
    private function makeController(
        ?array $payload = null,
        string $rawBody = '',
    ): TestableRestController {
        $controller = new TestableRestController('test', \Yii::$app);
        $controller->response = new Response();

        if ($payload !== null) {
            $rawBody = json_encode($payload, JSON_THROW_ON_ERROR);
        }

        \Yii::$app->set('request', $this->makeRequest($rawBody));

        return $controller;
    }

    private function makeRequest(string $rawBody): \yii\web\Request
    {
        return new class ($rawBody) extends \yii\web\Request {
            public function __construct(private readonly string $body)
            {
                parent::__construct([]);
            }

            public function getRawBody(): string
            {
                return $this->body;
            }
        };
    }
}

/**
 * Тестовый наследник, раскрывающий protected-методы RestController.
 */
final class TestableRestController extends RestController
{
    /**
     * @param array<mixed> $data
     * @return array<string, mixed>
     */
    public function exposeResponseOK(array $data = []): array
    {
        return $this->responseOK($data);
    }

    /**
     * @param array<mixed> $data
     * @return array<string, mixed>
     */
    public function exposeResponseCreated(array $data, string $location): array
    {
        return $this->responseCreated($data, $location);
    }

    public function exposeResponseNoContent(): void
    {
        $this->responseNoContent();
    }

    public function exposeGetPayload(): mixed
    {
        return $this->getPayload();
    }
}
