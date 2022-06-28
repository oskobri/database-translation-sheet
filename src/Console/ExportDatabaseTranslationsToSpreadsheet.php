<?php

namespace Oskobri\DatabaseTranslationSheet\Console;

use Illuminate\Console\Command;
use Oskobri\DatabaseTranslationSheet\Client\SpreadSheet;
use Oskobri\DatabaseTranslationSheet\SheetExporter;
use Oskobri\DatabaseTranslationSheet\Util;

class ExportDatabaseTranslationsToSpreadsheet extends Command
{
    protected $signature = 'database-translation-sheet:export';

    protected $description = 'Export all database translations to your Google Sheet';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();

        $this->info('Exporting translations to spreadsheet ...');

        try {
            collect(config('database-translation-sheet.models'))->each(function ($modelClass) use ($spreadsheet) {
                $model = new $modelClass();
                $sheetTitle = Util::snakeCaseToWords($model->getTable());

                $this->info("... $sheetTitle");

                (new SheetExporter($spreadsheet))->export($model);
            });

            $this->info('Done !');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
