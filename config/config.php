<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'application_name' => 'Database Translation Sheet',

    /*
    |--------------------------------------------------------------------------
    | Spreadsheet
    |--------------------------------------------------------------------------
    |
    | Spreadsheet ID that you have created in Google Sheets.
    */
    'spreadsheet_id' => env('DATABASE_TRANSLATION_SPREADSHEET_ID'),

    /*
    |--------------------------------------------------------------------------
    | Google Service Account Key and Email
    |--------------------------------------------------------------------------
    |
    | Service account key Json file path
    | Service account email
    */
    'service_account_key' => env('DATABASE_TRANSLATION_SPREADSHEET_SERVICE_ACCOUNT_KEY'),
    'service_account_email' => env('DATABASE_TRANSLATION_SPREADSHEET_SERVICE_ACCOUNT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Locales that you want to translate and report in your Google spreadsheet.
    */
    'locales' => explode(',', env('DATABASE_TRANSLATION_LOCALES', 'en')),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Models classnames that have translatable attributes.
    | Attention: Model must use Spatie translatable package and HasTranslations trait.
    | https://github.com/spatie/laravel-translatable
    */
    'models' => [
        // App\Models\Country::class,
        // App\Models\Language::class,
        // ...
    ]
];
