<?php

namespace Oskobri\DatabaseTranslationSheet\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Oskobri\DatabaseTranslationSheet\Client\SpreadSheet;
use Oskobri\DatabaseTranslationSheet\SheetImporter;

class ImportTranslationSheetToDatabase extends Command
{
    protected $signature = 'database-translation-sheet:import';

    protected $description = 'Import all translations sheets to your database';

    public function handle()
    {
        $this->info('Importing translations to database ...');

        try {
            DB::transaction(function () {
                ($spreadsheet = new Spreadsheet())
                    ->getSheets()
                    ->each(function ($sheet) use ($spreadsheet) {
                        $sheetTitle = $sheet->getProperties()->getTitle();

                        $this->info("... $sheetTitle");

                        (new SheetImporter($spreadsheet))->import($sheetTitle);
                    });
            });

            $this->info('Done !');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
