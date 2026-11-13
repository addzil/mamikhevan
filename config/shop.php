<?php

return [
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@mamikhevanshop.id'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'store' => [
        'name' => 'Mamikhevanshop',
        'address' => 'Jl. Lapangan Kobra 1 No.118, Tambun Selatan, Kab. Bekasi, 17510',
        'phone' => '085782194580',
        'whatsapp' => '6285782194580',
        'email' => 'adlipurna@gmail.com',
    ],

    'google_maps' => [
        'url' => 'https://www.google.com/maps/search/?api=1&query=mamikhevanshop%20Tambun%20Selatan',
        'rating' => 5.0,
        'review_count' => 25,
    ],
];
