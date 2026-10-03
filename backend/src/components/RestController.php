<?php

namespace CatRegistry\applications\components;

use yii\web\Response;
use Yii;

abstract class RestController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    protected function responseOK(array $data = []): array
    {
        $this->response->format = Response::FORMAT_JSON;
        $this->response->statusCode = 200;

        return [
            'code' => 200,
            'data' => $data
        ];
    }

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

    protected function getPayload(): array
    {
        return json_decode(Yii::$app->request->getRawBody(), true) ?? [];
    }
}
