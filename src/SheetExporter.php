<?php

namespace Oskobri\DatabaseTranslationSheet;

use Oskobri\DatabaseTranslationSheet\Client\SpreadSheet;

class SheetExporter
{
    private SpreadSheet $spreadsheet;

    public function __construct(SpreadSheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

    public function export($model)
    {
        $this->spreadsheet->writeSheet(
            Util::snakeCaseToWords($model->getTable()),
            (new Model($model))->getSheetRows()
        );
    }

}
