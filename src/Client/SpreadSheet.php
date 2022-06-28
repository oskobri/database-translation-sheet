<?php

namespace Oskobri\DatabaseTranslationSheet\Client;

use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_Spreadsheet;
use Google_Service_Sheets_ValueRange;

class SpreadSheet
{
    private $spreadsheetId;
    private Google_Service_Sheets $client;

    public function __construct()
    {
        $this->spreadsheetId = config('database-translation-sheet.spreadsheet_id');
        $this->client = new Google_Service_Sheets((new Client())->getClient());
    }

    public function getSpreadSheet(): Google_Service_Sheets_Spreadsheet
    {
        return $this->client
            ->spreadsheets
            ->get($this->spreadsheetId);
    }

    public function getSheets(): \Illuminate\Support\Collection
    {
        return collect($this->getSpreadSheet()->getSheets());
    }

    public function getSheetDetails($sheetTitle): Google_Service_Sheets_ValueRange
    {
        return $this->client->spreadsheets_values->get($this->spreadsheetId, $sheetTitle);
    }

    public function addSheet($sheetTitle)
    {
        $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                'addSheet' => [
                    'properties' => [
                        'title' => $sheetTitle
                    ]
                ]
            ]
        ]);

        $response = $this->client->spreadsheets->batchUpdate($this->spreadsheetId, $body);

        $this->updateSheetProperties($response->getReplies()[0]->getAddSheet()->getProperties()->getSheetId());
    }

    public function updateSheetProperties($sheetId)
    {
        $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                [
                    'addProtectedRange' => [
                        'protectedRange' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => 0,
                                'startColumnIndex' => 0,
                                'endColumnIndex' => 1,
                            ],
                            'editors' => [
                                'users' => [config('database-translation-sheet.service_account_email')]
                            ],
                            'warningOnly' => false,
                            'description' => 'Protecting first column'
                        ]
                    ]
                ],
                [
                    'addProtectedRange' => [
                        'protectedRange' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => 0,
                                'endRowIndex' => 1,
                                'startColumnIndex' => 0,
                            ],
                            'editors' => [
                                'users' => [config('database-translation-sheet.service_account_email')]
                            ],
                            'warningOnly' => false,
                            'description' => 'Protecting header'
                        ]
                    ]
                ],
                [
                    'updateSheetProperties' => [
                        'properties' => [
                            'sheetId' => $sheetId,
                            'gridProperties' => [
                                'frozenRowCount' => 1,
                            ],
                        ],
                        'fields' => 'gridProperties.frozenRowCount',
                    ],
                ]
            ]
        ]);

        $this->client->spreadsheets->batchUpdate($this->spreadsheetId, $body);
    }

    public function writeSheet($sheetTitle, $rows)
    {
        if (!$this->doesSheetExist($sheetTitle)) {
            $this->addSheet($sheetTitle);
        }

        $this->client
            ->spreadsheets_values
            ->update(
                $this->spreadsheetId,
                $sheetTitle,
                new Google_Service_Sheets_ValueRange(['values' => $rows]),
                ['valueInputOption' => 'RAW']
            );
    }

    public function doesSheetExist($sheetTitle): bool
    {
        foreach ($this->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetTitle) {
                return true;
            }
        }

        return false;
    }
}
