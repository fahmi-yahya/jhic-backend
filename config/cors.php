<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Harus mengizinkan origin frontend secara eksplisit (BUKAN '*') karena
    | supports_credentials => true. Browser akan menolak cookie kalau
    | allowed_origins masih wildcard.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Domain beneran (bukan pola/regex) ditaruh di sini — exact match,
    // jadi http:// dan https:// dihitung beda, harus dua-duanya didaftarkan
    // kalau dua-duanya dipakai akses situsnya.
    'allowed_origins' => [
        'http://blud.timevali.my.id',
        'https://blud.timevali.my.id',
    ],

    // Ini KHUSUS buat pola/regex (match banyak origin sekaligus, mis. semua
    // port localhost) — makanya tiap entry WAJIB diapit delimiter (#...#).
    // Domain tetap seperti blud.timevali.my.id TIDAK boleh ditaruh di sini
    // sebagai string biasa — itu dianggap pola regex yang tidak valid dan
    // diam-diam gagal match (ini bug yang baru saja terjadi).
    'allowed_origins_patterns' => [
        '#^http://localhost:\d+$#',
        '#^http://127\.0\.0\.1:\d+$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
