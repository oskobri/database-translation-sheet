<?php

namespace Oskobri\DatabaseTranslationSheet;

use Illuminate\Support\Facades\DB;
use Oskobri\DatabaseTranslationSheet\Client\SpreadSheet;

class SheetImporter
{
    private SpreadSheet $spreadsheet;

    public function __construct(SpreadSheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

    public function import($sheetTitle)
    {
        $sheetDetails = $this->spreadsheet->getSheetDetails($sheetTitle);
        $rows = collect($sheetDetails->getValues());
        $header = $this->formatHeaderToDatabaseColumn($rows->shift());
        $columnsCount = count($header);

        $rows->each(function ($row) use ($header, $sheetTitle, $columnsCount) {

            // Fill the missing columns with empty values
            $row = array_pad($row, $columnsCount, '');

            $columns = array_combine($header, $row);

            // Primary key is the first column
            $condition = array_splice($columns, 0, 1);

            DB::table(Util::wordsToSnakeCase($sheetTitle))
                ->where($condition)
                ->update($columns);
        });
    }


    protected function formatHeaderToDatabaseColumn($header): array
    {
        return array_map(function ($headerColumn) {
            return Util::headerLocaleToDatabaseColumn($headerColumn);
        }, $header);
    }

}
