<?php

namespace CatRegistry\applications\exceptions;

final class BadRequestHttpException extends \yii\web\BadRequestHttpException
{
    private $data;

    public function __construct(?array $message = null, $data = [], $code = 0, $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }
}
