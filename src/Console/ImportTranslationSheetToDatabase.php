<?php

namespace Oskobri\DatabaseTranslationSheet\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Oskobri\DatabaseTranslationSheet\Clients\SpreadSheet;
use Oskobri\DatabaseTranslationSheet\Util;

class ImportTranslationSheetToDatabase extends Command
{
    protected $signature = 'database-translation-sheet:import';

    protected $description = 'Import all translations sheets to your database';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();

        $this->info('Importing translations to database ...');

        foreach ($spreadsheet->getSheets() as $sheet) {
            $sheetTitle = $sheet->getProperties()->getTitle();
            $this->info("... $sheetTitle");

            $this->importTranslationSheetToDatabase($spreadsheet, $sheetTitle);
        }

        $this->info('Done !');
    }

    public function importTranslationSheetToDatabase($spreadsheet, $sheetTitle)
    {
        try {
            $sheetDetails = $spreadsheet->getSheetDetails($sheetTitle);
            $rows = collect($sheetDetails->getValues());
            $header = $this->formatHeaderToDatabaseColumn($rows->shift());
            $columnsCount = count($header);

            $rows->each(function ($row) use ($header, $sheetTitle, $columnsCount) {

                // Fill the missing columns with empty values
                $row = array_pad($row,  $columnsCount, '');

                $columns = array_combine($header, $row);

                // Primary key is the first column
                $condition = array_splice($columns, 0, 1);

                DB::table(Util::wordsToSnakeCase($sheetTitle))
                    ->where($condition)
                    ->update($columns);
            });
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            dd($rows);
        }
    }

    public function formatHeaderToDatabaseColumn($header): array
    {
        return array_map(function ($headerColumn) {
            return Util::headerLocaleToDatabaseColumn($headerColumn);
        }, $header);
    }
}
