<?php

return [
    'version' => env('APP_VERSION'),
    'uploads_disk' => env('UPLOADS_DISK', 'uploads_local'),
    'demo'    => env('IS_DEMO_SITE', false),
    'disable_update_auth' => env('DISABLE_UPDATE_AUTH', false),
    'use_symlinks' => env('USE_SYMLINKS', false),
    'user_model' => \Common\Auth\User::class,
    'enable_contact_page' => env('ENABLE_CONTACT_PAGE', false),
];