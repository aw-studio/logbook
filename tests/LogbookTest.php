<?php

use AwStudio\Logbook\Logbook;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

afterEach(function () {
    fclose(fopen(getLogbookForTests(), 'w'));
});

beforeEach(function () {
    Config::set('logbook.channel', 'file');
});

test('it can log to file', function () {
    Config::set('logbook.channel', 'file');
    $logbook = new Logbook();
    $logbook->log('test', 'test', ['test' => 'test']);
    $this->assertFileExists(storage_path('logs/logbook.log'));
    $this->assertStringContainsString('test', file_get_contents(storage_path('logs/logbook.log')));
});

test('It can log to api', function () {
    Config::set('logbook', [
        'channel' => 'api',
        'api_endpoint' => 'api.test',
        'project_token' => 'PROJEKT_TOKEN',
    ]);

    Http::fake([
        '*' => Http::response(),
    ]);

    $logbook = new Logbook();
    $logbook->log('test');

    $logContent = file_get_contents($logbook->getLogPathForTempApiLog());

    unset($logbook);

    Http::assertSent(function ($request) use ($logContent) {
        return $request->url() === 'api.test/' &&
            $request['project_token'] === 'PROJEKT_TOKEN' &&
            $request['log'] === $logContent;
    });

    Http::assertSentCount(1);
});

test('It logs the caller_information', function () {
    $logbook = new Logbook();
    $logbook->log('test');
    $logs = parseLogFile();

    expect($logs[0]['occurrence']['file'])
        ->toContain(realpath(__DIR__).'/LogbookTest.php');
    expect($logs[0]['occurrence']['class'])->toContain('LogbookTest');
});

test('It logs the batch id when a batch was opened', function () {
    $logbook = new Logbook();
    $logbook->open();
    $logbook->log('test');
    $logs = parseLogFile();

    expect($logs[0]['batch_id'])->toBe($logbook->getBatch()->getUuid());
});

test('It logs the batch name when a batch was opened', function () {
    $logbook = new Logbook();
    $logbook->open('foo_batch');
    $logbook->log('test');
    $logs = parseLogFile();
    expect($logs[0]['batch_name'])->toBe($logbook->getBatch()->getName());
});

test('It logs different batches with different batch ids', function () {
    $logbook = new Logbook();
    $logbook->open();
    $logbook->log('test');
    $logbook->close();

    $logbook->open();
    $logbook->log('test');
    // dont close the batch

    $logs = parseLogFile();

    expect($logs[0]['batch_id'])->not->toBe($logs[1]['batch_id']);
    expect($logs[1]['batch_id'])->toBe($logbook->getBatch()->getUuid());
});
