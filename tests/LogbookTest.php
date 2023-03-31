<?php

use AwStudio\Logbook\Logbook;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

test('it can log to file', function () {
    Config::set('logbook.channel', 'file');
    $logbook = new Logbook();
    $logbook->log('Foo bar baz');
    expect(storage_path('logs/logbook.log'))->toBeReadableFile();
    expect(file_get_contents(storage_path('logs/logbook.log')))->toContain('Foo bar baz');
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

    expect(latestLogEntry()['occurrence']['file'])
        ->toContain(realpath(__DIR__).'/LogbookTest.php');
    expect(latestLogEntry()['occurrence']['class'])->toContain('LogbookTest');
});

test('It logs the batch id when a batch was opened', function () {
    $logbook = new Logbook();
    $logbook->open();
    $logbook->log('test');

    expect(latestLogEntry()['batch_id'])->toBe($logbook->getBatch()->getUuid());
});

test('It logs the batch name when a batch was opened', function () {
    $logbook = new Logbook();
    $logbook->open('foo_batch');
    $logbook->log('test');
    expect(latestLogEntry()['batch_name'])->toBe($logbook->getBatch()->getName());
});

test('It logs different batches with different batch ids', function () {
    $logbook = new Logbook();
    $logbook->open();
    $logbook->log('test');
    $logbook->close();

    $logbook->open();
    $logbook->log('test');
    // dont close the batch

    expect(latestLogEntry()['batch_id'])->not->toBe(parseLogFile()[0]['batch_id']);
    expect(latestLogEntry()['batch_id'])->toBe($logbook->getBatch()->getUuid());
});

afterEach(function () {
    fclose(fopen(getLogbookForTests(), 'w'));
});

beforeEach(function () {
    Config::set('logbook.channel', 'file');
});
