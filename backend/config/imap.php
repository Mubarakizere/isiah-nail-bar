<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Account
    |--------------------------------------------------------------------------
    |
    | The default account identifier. It will be used as default for any missing account parameters.
    | If however the default account is not found in the accounts list, the first account will be used.
    |
    */
    'default' => env('IMAP_DEFAULT_ACCOUNT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Available Accounts
    |--------------------------------------------------------------------------
    |
    | Please list all available accounts within this array.
    |
    */
    'accounts' => [

        'default' => [// account identifier
            'host'  => env('IMAP_HOST', env('MAIL_HOST', 'localhost')),
            'port'  => env('IMAP_PORT', 993),
            'protocol'  => env('IMAP_PROTOCOL', 'imap'), //might also use imap, [pop3 or nntp (unsupported)]
            'encryption' => env('IMAP_ENCRYPTION', 'ssl'), // Supported: false, 'ssl', 'tls', 'starttls', 'notls'
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'username' => env('IMAP_USERNAME', env('MAIL_USERNAME', 'root@example.com')),
            'password' => env('IMAP_PASSWORD', env('MAIL_PASSWORD', '')),
            'authentication' => env('IMAP_AUTHENTICATION', null),
            'proxy' => [
                'socket' => null,
                'request_fulluri' => false,
                'username' => null,
                'password' => null,
            ],
            "timeout" => 30,
            "extensions" => []
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available options
    |--------------------------------------------------------------------------
    |
    |
    */
    'options' => [
        'delimiter' => '/',
        'fetch' => \Webklex\PHPIMAP\Query\WhereQuery::class,
        'sequence' => \Webklex\PHPIMAP\Query\WhereQuery::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Events
    |--------------------------------------------------------------------------
    |
    */
    'events' => [
        "message" => [
            "new" => \Webklex\IMAP\Events\MessageNewEvent::class,
            "moved" => \Webklex\IMAP\Events\MessageMovedEvent::class,
            "deleted" => \Webklex\IMAP\Events\MessageDeletedEvent::class,
            "restore" => \Webklex\IMAP\Events\MessageRestoreEvent::class,
        ],
        "folder" => [
            "new" => \Webklex\IMAP\Events\FolderNewEvent::class,
            "moved" => \Webklex\IMAP\Events\FolderMovedEvent::class,
            "deleted" => \Webklex\IMAP\Events\FolderDeletedEvent::class,
        ],
        "flag" => [
            "new" => \Webklex\IMAP\Events\FlagNewEvent::class,
            "deleted" => \Webklex\IMAP\Events\FlagDeletedEvent::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Masks
    |--------------------------------------------------------------------------
    |
    */
    'masks' => [
        'message' => \Webklex\PHPIMAP\Support\Masks\MessageMask::class,
        'attachment' => \Webklex\PHPIMAP\Support\Masks\AttachmentMask::class,
    ]
];
