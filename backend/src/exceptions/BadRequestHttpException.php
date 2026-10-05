<?php

namespace CatRegistry\applications\exceptions;

use Throwable;

final class BadRequestHttpException extends \yii\web\BadRequestHttpException
{
    /**
     * @var array<string, string[]>
     */
    private $data;

    /**
     * @param array<string, string[]> $data
     */
    public function __construct(
        array $data = [],
        string $message = 'Ошибка валидации',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, string[]>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
