<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenLiteSpeed multi-tenant isolation system
    |
    */

    'base_path' => '/var/www/html/multisite',
    
    'php' => [
        'version' => '8.1',
        'binary' => '/usr/local/lsws/lsphp81/bin/lsphp',
        'socket_prefix' => 'uds://tmp/lsphp_',
        'ini_template' => 'multitenant/php.ini.template',
    ],

    'openlitespeed' => [
        'config_dir' => '/usr/local/lsws/conf',
        'vhosts_dir' => '/usr/local/lsws/conf/vhosts-enabled',
        'httpd_config' => '/usr/local/lsws/conf/httpd_config.xml',
        'log_dir' => '/usr/local/lsws/logs',
        'admin_port' => 7080,
        'restart_command' => '/usr/local/lsws/bin/lswsctrl restart',
        'reload_command' => '/usr/local/lsws/bin/lswsctrl reload',
    ],

    'security' => [
        'file_permissions' => [
            'directories' => 0750,
            'files' => 0640,
            'php_files' => 0644,
        ],
        'user_prefix' => 'sid_',
        'group' => 'www-data',
    ],

    'templates' => [
        'vhost' => 'multitenant/vhost.xml.template',
        'external_app' => 'multitenant/external_app.xml.template',
        'php_ini' => 'multitenant/php.ini.template',
    ],

    'directories' => [
        'public' => 'public',
        'logs' => 'logs',
        'tmp' => 'tmp',
        'php' => 'php',
    ],
];