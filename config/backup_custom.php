<?php

return [
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'message_thread_id' => env('TELEGRAM_MESSAGE_THREAD_ID'),
        'max_upload_size' => env('TELEGRAM_MAX_UPLOAD_SIZE', 50) * 1024 * 1024, // Convert MB to Bytes
    ],
    'rclone' => [
        'remote_name'    => env('RCLONE_REMOTE_NAME', 'gdrive'),
        'folder'         => env('RCLONE_FOLDER', 'backup'),
        'client_id'      => env('RCLONE_CLIENT_ID', ''),
        'client_secret'  => env('RCLONE_CLIENT_SECRET', ''),
        'refresh_token'  => env('RCLONE_REFRESH_TOKEN', ''),
    ],
    'storage_path' => env('BACKUP_STORAGE_PATH', 'storage/app/backups'),
    'retention_days' => env('BACKUP_RETENTION_DAYS', 7),
    'password' => env('BACKUP_PASSWORD'),
];
