<?php

namespace Oskobri\DatabaseTranslationSheet\Console;

use Illuminate\Console\Command;
use Oskobri\DatabaseTranslationSheet\Clients\SpreadSheet;
use Oskobri\DatabaseTranslationSheet\Translations\Model;
use Oskobri\DatabaseTranslationSheet\Util;

class ExportDatabaseTranslationsToSpreadsheet extends Command
{
    protected $signature = 'database-translation-sheet:export';

    protected $description = 'Export all database translations to your Google Sheet';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();

        $this->info('Exporting translations to spreadsheet ...');

        collect(config('database-translation-sheet.models'))->each(function ($modelClass) use ($spreadsheet) {
            $model = new $modelClass();

            $sheetTitle = Util::snakeCaseToWords($model->getTable());
            $this->info("... $sheetTitle");

            $spreadsheet->writeSheet(
                Util::snakeCaseToWords($model->getTable()),
                (new Model($model))->getSheetRows()
            );
        });

        $this->info('Done !');
    }
}
