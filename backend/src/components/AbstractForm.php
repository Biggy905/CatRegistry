<?php

namespace CatRegistry\applications\components;

use yii\base\DynamicModel;
use yii\validators\Validator;

/**
 *
 */
abstract class AbstractForm extends DynamicModel
{
    protected static string $formName = '';

    public function formName(): string
    {
        return static::$formName;
    }

    /**
     * @param mixed $request
     * @param mixed|null $attributes
     * @param mixed|null $validator
     * @param ?array<mixed> $options
     * @return bool
     */
    public function runValidate(
        mixed $request,
        mixed $attributes = null,
        mixed $validator = null,
        ?array $options = null,
    ): bool {
        /** @var array<mixed> $request*/
        $this->load($request);

        if (
            !empty($attributes)
            && $validator instanceof Validator
            && isset($options)
        ) {
            /** @var array<mixed>|string $attributes */
            $this->addRule($attributes, $validator, $options);
        }

        return $this->validate();
    }

    /**
     * @return array<string, string>
     */
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
