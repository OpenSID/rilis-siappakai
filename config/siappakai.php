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
    'openlitespeed' => [
        'listeners_config' => env('OLS_LISTENERS_CONFIG', '/usr/local/lsws/conf/listeners.d/listeners.conf'),
        'httpd_config' => env('OLS_HTTPD_CONFIG', '/usr/local/lsws/conf/httpd_config.conf'),
        'vhosts_enabled' => env('OLS_VHOSTS_ENABLED', '/usr/local/lsws/conf/vhosts-enabled'),
    ],
    'ssl' => [
        'enabled' => env('SSL_ENABLED', true),
        'acme_path' => env('ACME_PATH', '/root/.acme.sh/acme.sh'),
        'cert_directory' => env('SSL_CERT_DIRECTORY', '/usr/local/lsws/conf/cert'),
        'default_email' => env('SSL_DEFAULT_EMAIL', 'admin@opensid.my.id'),
        'ca_server' => env('SSL_CA_SERVER', 'letsencrypt'),
        'staging' => env('SSL_STAGING', false),
        'webroot' => env('SSL_WEBROOT', '/var/www/html'),
        'auto_renew' => env('SSL_AUTO_RENEW', true),
        'renew_days_before' => env('SSL_RENEW_DAYS_BEFORE', 30),
    ],
];
