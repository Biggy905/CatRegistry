<?php

namespace CatRegistry\applications\enums;

enum CatGenderEnums: string
{
    case CAT_GENDER_MALE = 'male';
    case CAT_GENDER_FEMALE = 'female';

    /**
     * @return array<int, string>
     */
    public static function toArray(): array
    {
        $data = [];
        $cases = CatGenderEnums::cases();

        foreach ($cases as $case) {
            $data[] = $case->value;
        }

        return $data;
    }
}
