<?php

return [
    // --- Родители (female) ---
    [
        'id' => 100,
        'name' => 'Mother A',
        'gender' => 'female',
        'age' => 10,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],
    [
        'id' => 101,
        'name' => 'Mother B',
        'gender' => 'female',
        'age' => 9,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],
    [
        'id' => 102,
        'name' => 'Mother C',
        'gender' => 'female',
        'age' => 11, 'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Родители (male) ---
    [
        'id' => 200,
        'name' => 'Father 1',
        'gender' => 'male',
        'age' => 8,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],
    [
        'id' => 201,
        'name' => 'Father 2',
        'gender' => 'male',
        'age' => 9,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],
    [
        'id' => 202,
        'name' => 'Father 3',
        'gender' => 'male',
        'age' => 10,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],
    [
        'id' => 203,
        'name' => 'Father 4',
        'gender' => 'male',
        'age' => 11,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Сценарий 1: без матери и без отца ---
    [
        'id' => 1,
        'name' => 'Orphan',
        'gender' => 'male',
        'age' => 2, 'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Сценарий 2: мать есть, отца нет ---
    [
        'id' => 2,
        'name' => 'WithMother',
        'gender' => 'male',
        'age' => 2, 'mother_id' => 100,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Сценарий 3: мать + один отец ---
    [
        'id' => 3,
        'name' => 'OneFather',
        'gender' => 'male',
        'age' => 2, 'mother_id' => 100,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Сценарий 4: мать + три отца ---
    [
        'id' => 4,
        'name' => 'ThreeFathers',
        'gender' => 'male',
        'age' => 2,
        'mother_id' => 101,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Сценарий 5: без матери, один отец ---
    [
        'id' => 5,
        'name' => 'OnlyFather',
        'gender' => 'male',
        'age' => 2,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],

    // --- Сценарий 6: без матери, три отца ---
    [
        'id' => 6,
        'name' => 'ThreeFathersNoMother',
        'gender' => 'male',
        'age' => 2,
        'mother_id' => null,
        'created_at' => '2025-01-01 00:00:00'
    ],
];
