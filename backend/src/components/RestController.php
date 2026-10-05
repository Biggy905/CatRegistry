<?php

namespace CatRegistry\applications\components;

use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;
use yii\web\Request;
use yii\web\Response;
use Yii;

abstract class RestController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    /**
     * @param array<mixed> $data
     * @return array<string, mixed>
     */
    protected function responseOK(array $data = []): array
    {
        $this->response->format = Response::FORMAT_JSON;
        $this->response->statusCode = 200;

        return [
            'code' => 200,
            'data' => $data
        ];
    }

    /**
     * @param array<mixed> $data
     * @param string $location
     * @return array<string, mixed>
     */
    protected function responseCreated(array $data, string $location): array
    {
        $this->response->format = Response::FORMAT_JSON;
        $this->response->statusCode = 201;
        $this->response->headers->set('Location', $location);

        return [
            'code' => 201,
            'data' => $data
        ];
    }

    protected function responseNoContent(): void
    {
        $this->response->statusCode = 204;
        $this->response->format = Response::FORMAT_JSON;
    }

    protected function getPayload(): mixed
    {
        /** @var ConsoleApplication|WebApplication $app */
        $app = Yii::$app;
        /** @var Request $request */
        $request = $app->request;

        return json_decode($request->getRawBody(), true) ?? [];
    }
}
