<?php

return [
    // Сценарий 3: cat 3 → father 200
    [
        'id' => 1, 
        'cat_id' => 3, 
        'father_id' => 200,
    ],

    // Сценарий 4: cat 4 → fathers 200, 201, 202
    [
        'id' => 2, 
        'cat_id' => 4, 
        'father_id' => 200,
    ],
    [
        'id' => 3, 
        'cat_id' => 4, 
        'father_id' => 201,
    ],
    [
        'id' => 4, 
        'cat_id' => 4, 
        'father_id' => 202,
    ],

    // Сценарий 5: cat 5 → father 203
    [
        'id' => 5, 
        'cat_id' => 5, 
        'father_id' => 203,
    ],

    // Сценарий 6: cat 6 → fathers 200, 201, 203
    [
        'id' => 6, 
        'cat_id' => 6, 
        'father_id' => 200,
    ],
    [
        'id' => 7, 
        'cat_id' => 6, 
        'father_id' => 201,
    ],
    [
        'id' => 8, 
        'cat_id' => 6, 
        'father_id' => 203,
    ],
];
