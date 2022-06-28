<?php

namespace Oskobri\DatabaseTranslationSheet\Client;

class Client
{
    private \Google_Client $client;

    public function __construct()
    {
        if (!config('database-translation-sheet.service_account_key') || !file_exists(config('database-translation-sheet.service_account_key'))) {
            throw new \Exception('You must set the service_account_key key in your config/database-translation-sheet.php file');
        }

        $this->client = new \Google_Client();

        $this->client->setApplicationName(config('database-translation-sheet.application_name'));
        $this->client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $this->client->setAuthConfig(config('database-translation-sheet.service_account_key'));
    }

    public function getClient(): \Google_Client
    {
        return $this->client;
    }
}
