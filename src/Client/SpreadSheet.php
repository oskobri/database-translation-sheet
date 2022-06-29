<?php

namespace Oskobri\DatabaseTranslationSheet\Client;

use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_Spreadsheet;
use Google_Service_Sheets_ValueRange;

class SpreadSheet
{
    private $spreadsheetId;
    private Google_Service_Sheets $client;
    private \Illuminate\Support\Collection $sheets;

    public function __construct()
    {
        $this->spreadsheetId = config('database-translation-sheet.spreadsheet_id');
        $this->client = new Google_Service_Sheets((new Client())->getClient());
        $this->sheets = collect();
    }

    public function getSpreadSheet(): Google_Service_Sheets_Spreadsheet
    {
        return $this->client
            ->spreadsheets
            ->get($this->spreadsheetId);
    }

    public function getSheets(): \Illuminate\Support\Collection
    {
        if ($this->sheets->isEmpty()) {
            $this->sheets = collect($this->getSpreadSheet()->getSheets());
        }

        return $this->sheets;
    }

    public function resetSheets(): SpreadSheet
    {
        $this->sheets = collect();

        return $this;
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

        $this->initSheetProperties($response->getReplies()[0]->getAddSheet()->getProperties()->getSheetId());
    }

    public function initSheetProperties($sheetId)
    {
        $this->sendBatchRequests([
            new Google_Service_Sheets_Request([
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
            ]),
            new Google_Service_Sheets_Request([
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
            ]),
            new Google_Service_Sheets_Request([
                'updateSheetProperties' => [
                    'properties' => [
                        'sheetId' => $sheetId,
                        'gridProperties' => [
                            'frozenRowCount' => 1,
                        ],
                    ],
                    'fields' => 'gridProperties.frozenRowCount',
                ],
            ])
        ]);
    }

    protected function sendBatchRequests($requests)
    {
        $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $this->client->spreadsheets->batchUpdate($this->spreadsheetId, $body);
    }

    public function writeSheet($sheetTitle, $rows)
    {
        $sheetId = $this->getSheetIdFromTitle($sheetTitle);
        $columnsCount = count($rows[0]);

        if (!$this->doesSheetExist($sheetTitle)) {
            $this->addSheet($sheetTitle);
        }


        $requests = [
            $this->headerColorRequest($sheetId, $columnsCount),
            $this->columnSizeRequest($sheetId, $columnsCount)
        ];

        $this->sendBatchRequests($requests);

        $this->client
            ->spreadsheets_values
            ->update(
                $this->spreadsheetId,
                $sheetTitle,
                new Google_Service_Sheets_ValueRange(['values' => $rows]),
                ['valueInputOption' => 'RAW']
            );
    }

    public function getSheetIdFromTitle($sheetTitle)
    {
        $sheet = $this->getSheets()->filter(function ($sheet) use ($sheetTitle) {
            return $sheet->getProperties()->getTitle() == $sheetTitle;
        })->first();

        if (!$sheet) {
            return null;
        }

        return $sheet->getProperties()->getSheetId();
    }

    public function doesSheetExist($sheetTitle): bool
    {
        return !!$this->getSheetIdFromTitle($sheetTitle);
    }


    protected function headerColorRequest($sheetId, $columnsCount): Google_Service_Sheets_Request
    {
        return new Google_Service_Sheets_Request([
            'repeatCell' => [
                'cell' => [
                    'userEnteredFormat' => [
                        'backgroundColor' => [
                            'red' => 135,
                            'green' => 140,
                            'blue' => 140
                        ],
                        'horizontalAlignment' => 'LEFT',
                        'textFormat' => [
                            'foregroundColor' => [
                                'red' => 1.0,
                                'green' => 1.0,
                                'blue' => 1.0
                            ],
                            'fontSize' => 10,
                            'bold' => true
                        ]
                    ],
                ],
                'range' => [
                    'sheetId' => $sheetId,
                    'startRowIndex' => 0,
                    'endRowIndex' => 1,
                    'startColumnIndex' => 0,
                    'endColumnIndex' => $columnsCount
                ],
                'fields' => 'userEnteredFormat'
            ]
        ]);
    }

    protected function columnSizeRequest($sheetId, $columnsCount): Google_Service_Sheets_Request
    {
        return new Google_Service_Sheets_Request([
            'updateDimensionProperties' => [
                'range' => [
                    'sheetId' => $sheetId,
                    'dimension' => 'COLUMNS',
                    'startIndex' => 0,
                    'endIndex' => $columnsCount
                ],
                'properties' => [
                    'pixelSize' => 200
                ],
                'fields' => 'pixelSize'
            ]
        ]);
    }
}
