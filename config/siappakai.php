<?php

return [
    'sandi' => [
        'token' => env('TOKEN_PANTAU', '1|F8LgbYeG8t5tSFsFz7qgBD3Z0lLPVUe0jDY9ofvt'),
    ],
    'pantau' => [
        'api_pantau' => env('API_PANTAU', 'https://pantau.opensid.my.id/api'),
        'token_pantau' => env('TOKEN_PANTAU', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6bnVsbCwidGltZXN0YW1wIjoxNjAzNDY2MjM5fQ.HVCNnMLokF2tgHwjQhSIYo6-2GNXB4-Kf28FSIeXnZw'),
    ],
    'root' => [
        'folder' => env('ROOT_OPENSID', '/var/www/html/'),
        'folder_multisite' => env('MULTISITE_OPENSID', '/var/www/html/multisite/'),
    ],
    'git' => [
        'token' => env('GIT_TOKEN', ''),
    ],
];
