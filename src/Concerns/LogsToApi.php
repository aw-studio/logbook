<?php

namespace AwStudio\Logbook\Concerns;

trait LogsToApi
{
    protected function checkApiChannelConfiguration()
    {
        if (! config('logbook.project_token')) {
            throw new \Exception('Logbook project not set.');
        }
        if (! config('logbook.api_endpoint')) {
            throw new \Exception('Logbook API endpoint not set.');
        }
    }

    protected function sendLogsToApi()
    {
        if (! $content = $this->getLogContent()) {
            return;
        }

        $request = $this->client->post('/', [
            'project_token' => config('logbook.project_token'),
            'log' => $content,
        ]);

        if ($request->successful()) {
            $this->cleanupTempApiLogbookLogsFile();
        }
    }

    protected function getLogContent()
    {
        try {
            return file_get_contents($this->getLogPathForTempApiLog());
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getLogPathForTempApiLog()
    {
        return storage_path('/logs/logbook_temp.log');
    }

    public function cleanupTempApiLogbookLogsFile()
    {
        fclose(fopen($this->getLogPathForTempApiLog(), 'w'));
    }
}
