<?php

use Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function getLogbookForTests()
{
    return storage_path('logs/logbook.log');
}

function parseLogFile()
{
    $logFileContent = file_get_contents(getLogbookForTests());
    $logs = explode(PHP_EOL, $logFileContent);

    $logs = array_filter($logs, function ($log) {
        return ! empty($log);
    });

    $logs = array_map(function ($log) {
        return json_decode($log, true);
    }, $logs);

    return $logs;
}
