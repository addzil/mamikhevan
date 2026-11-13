<?php

return [
    'origin' => [
        'city' => 'Kabupaten Bekasi',
        'district' => 'Tambun Selatan',
        'subdistrict' => 'Tambun',
        'postal_code' => '17510',
    ],

    'assumed_item_weight_grams' => 500,

    /** Tarif simulasi internal toko per kilogram, bukan tarif resmi ekspedisi. */
    'internal_rates' => [
        'metro' => ['jne' => 20000, 'jnt' => 18000],
        'java' => ['jne' => 30000, 'jnt' => 28000],
        'other' => ['jne' => 45000, 'jnt' => 42000],
    ],

    'gosend_sameday_estimate' => 35000,

    'komerce_origin_id' => env('KOMERCE_ORIGIN_ID'),
];
