<?php

namespace CatRegistry\applications\components;

use CatRegistry\applications\exceptions\BadRequestHttpException;
use yii\base\UserException;
use yii\web\Response;
use LogicException;
use DomainException;
use Throwable;

final class ErrorHandler extends \yii\web\ErrorHandler
{
    protected function renderException($exception)
    {
        $response = new Response();
        $response->data = [
            'code' => 500,
            'message' => 'Unknown Error',
        ];

        if($exception instanceof BadRequestHttpException) {
            $response->setStatusCode($exception->statusCode);

            $response->data = [
                'code' => $exception->statusCode,
                'name' => $exception->getName(),
                'errors' => $exception->getData(),
            ];
        } elseif ($exception instanceof DomainException) {
            $code = $exception->getCode() === 0 ? 500 : $exception->getCode();
            $response->setStatusCode($code);

            $response->data = [
                'code' => $code,
                'message' => $exception->getMessage(),
            ];
        } elseif ($exception instanceof LogicException) {
            $code = $exception->getCode() === 0 ? 500 : $exception->getCode();
            $response->setStatusCode($code);

            $response->data = [
                'code' => $code ?: 500,
                'message' => $exception->getMessage(),
            ];
        } elseif ($exception instanceof Throwable) {
            $response->setStatusCode($exception->statusCode ?? 500);
            $response->data = [
                'code' => $exception->statusCode ?? 500,
                'message' => $this->convertExceptionToArray($exception),
            ];
        } else {
            $response->setStatusCode($exception->statusCode ?? 500);
            $response->data = [
                'code' => $exception->statusCode ?? 500,
                'message' => $this->convertExceptionToArray($exception),
            ];
        }

        $response->format = Response::FORMAT_JSON;
        $response->send();
    }

    protected function convertExceptionToArray($exception): array
    {
        $name = 'Exception';
        if ($exception instanceof \yii\base\Exception || $exception instanceof \yii\base\ErrorException) {
            $name = $exception->getName();
        }

        $array = [
            'name' => $name,
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];

        $array['type'] = get_class($exception);
        if (!$exception instanceof UserException) {
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
            if ($exception instanceof \yii\db\Exception) {
                $array['error-info'] = $exception->errorInfo;
            }
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->convertExceptionToArray($prev);
        }

        return $array;
    }
}