<?php

namespace Oskobri\DatabaseTranslationSheet;

use Illuminate\Support\ServiceProvider;
use Oskobri\DatabaseTranslationSheet\Console\ExportDatabaseTranslationsToSpreadsheet;
use Oskobri\DatabaseTranslationSheet\Console\ImportTranslationSheetToDatabase;

class DatabaseTranslationSheetServiceProvider extends ServiceProvider
{
public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('database-translation-sheet.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportDatabaseTranslationsToSpreadsheet::class,
                ImportTranslationSheetToDatabase::class
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'database-translation-sheet');
    }



}
