<?php

return [
    'issuer_name' => env('OPEN_BADGES_ISSUER_NAME', 'Pangasinan State University'),
    'private_key_path' => env('OPEN_BADGES_PRIVATE_KEY_PATH', storage_path('app/private/open-badges/signing-key.pem')),
];
