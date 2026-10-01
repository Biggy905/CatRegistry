<?php

namespace CatRegistry\applications\components;

use yii\validators\Validator;

abstract class AbstractForm extends \yii\base\Model
{
    protected static string $formName = '';

    public function formName(): string
    {
        return static::$formName;
    }

    public function runValidate(
        array $request,
              $attributes = null,
              $validator = null,
        ?array $options = null,
    ): bool {
        $this->load($request);

        if (
            !empty($attributes)
            && $validator instanceof Validator
            && isset($options)
        ) {
            $this->addRule($attributes, $validator, $options);
        }

        return $this->validate();
    }

    public function getDataErrors(): array
    {
        $data = [];

        $errors = $this->getErrors();

        foreach ($errors as $attribute => $error) {
            $key = array_key_first($error);

            $data[$attribute] = $error[$key];
        }

        return $data;
    }
}
