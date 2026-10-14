<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Class Namespace
    |---------------------------------------------------------------------------
    */
    'class_namespace' => 'App\\Livewire',

    /*
    |---------------------------------------------------------------------------
    | View Path
    |---------------------------------------------------------------------------
    */
    'view_path' => resource_path('views/livewire'),

    /*
    |---------------------------------------------------------------------------
    | Layout
    |---------------------------------------------------------------------------
    */
    'layout' => 'components.layouts.app',

    /*
    |---------------------------------------------------------------------------
    | Lazy Loading Placeholder
    |---------------------------------------------------------------------------
    */
    'lazy_placeholder' => null,

    /*
    |---------------------------------------------------------------------------
    | Temporary File Uploads
    |---------------------------------------------------------------------------
    |
    | Livewire stages files locally before validation. We explicitly specify
    | the 'local' disk so temporary uploads are handled instantly by the
    | local web server container without making remote S3 network calls.
    |
    */
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TMP_DISK', (env('FILESYSTEM_DISK') === 's3' || !empty(env('AWS_BUCKET')) ? 's3' : 'local')),
        'rules' => null,
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => false,
    ],

    /*
    |---------------------------------------------------------------------------
    | Render On Redirect
    |---------------------------------------------------------------------------
    */
    'render_on_redirect' => false,

    /*
    |---------------------------------------------------------------------------
    | Back Button Cache
    |---------------------------------------------------------------------------
    */
    'back_button_cache' => false,

    /*
    |---------------------------------------------------------------------------
    | Navigate (SPA Mode)
    |---------------------------------------------------------------------------
    */
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#60a5fa',
    ],

];
