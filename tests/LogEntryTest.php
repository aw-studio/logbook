<?php

use AwStudio\Logbook\LogEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

test('It sets the given payload', function () {
    $logbook = new LogEntry('test');

    expect($logbook->payload)->toBe('test');
    expect($logbook->loggedAt->format('y-m-d H:i:s'))
        ->toEqual(now()->format('y-m-d H:i:s'));
});

test('It sets the given tags', function () {
    $logbook = (new LogEntry('test'))->setTag('foo');

    expect($logbook->tags)->toBe(['foo']);
});

test('It writes the logEntry to the logbook file when destructed', function () {
    $logbook = new LogEntry('test');
    // this will trigger the __destruct method
    unset($logbook);

    expect(latestLogEntry()['payload'])->toBe([
        'message' => 'test',
    ]);
});

test('It sets the batch name', function () {
    $logbook = (new LogEntry('test'))->setBatchName('foo');
    expect($logbook->batchName)->toBe('foo');
});

test('It sets the batch id', function () {
    $logbook = (new LogEntry('test'))->setBatchId('foo');
    expect($logbook->batchId)->toBe('foo');
});

test('It sets the caller information', function () {
    $logbook = (new LogEntry('test'))->setOccurrence(
        ['file' => 'foo', 'line' => 'bar']
    );
    expect($logbook->occurrence)->toBe([
        'file' => 'foo',
        'line' => 'bar',
    ]);
    expect($logbook->location)->toBe('foo:bar');
});

test('It sets the location', function () {
    $logbook = (new LogEntry('test'))->setLocation('foo');
    expect($logbook->location)->toBe('foo');
});

test('It doesnt include the caller Information when setWithCaller', function () {
    $logbook = (new LogEntry('test'))
        ->setOccurrence([
            'file' => 'foo',
            'line' => 'bar',
        ])
        ->dontLogOccurrence();

    unset($logbook);
    expect(latestLogEntry()['occurrence'])->toBe(null);
});

test('It logs with all the information', function () {
    Carbon::setTestNow($now = now());
    ray($now)->raw();
    $logbook = (new LogEntry('test'))
        ->setOccurrence([
            'file' => 'foo',
            'line' => 'bar',
        ])
        ->setDescription('baz')
        ->setBatchId('123456789')
        ->setBatchName('BatchName');

    unset($logbook);
    expect(latestLogEntry())->toHaveKeys(
        [
            'payload',
            'occurrence',
            'description',
            'batch_id',
            'batch_name',
            'model_type',
            'model_id',
            'location',
            'tags',
            'type',
            'logged_at',
        ]);

    expect(Carbon::parse(latestLogEntry()['logged_at'])->format('y-m-d H:i:s'))
        ->toEqual(now()->format('y-m-d H:i:s'));
    expect(latestLogEntry()['payload'])->toBe([
        'message' => 'test',
    ]);
    expect(latestLogEntry()['occurrence'])->toBe([
        'file' => 'foo',
        'line' => 'bar',
    ]);
    expect(latestLogEntry()['description'])->toBe('baz');
    expect(latestLogEntry()['batch_id'])->toBe('123456789');
    expect(latestLogEntry()['batch_name'])->toBe('BatchName');
    expect(latestLogEntry()['model_type'])->toBe(null);
    expect(latestLogEntry()['model_id'])->toBe(null);
    expect(latestLogEntry()['location'])->toBe('foo:bar');
    expect(latestLogEntry()['tags'])->toBe([]);
    expect(latestLogEntry()['type'])->toBe('log');
});

afterEach(function () {
    fclose(fopen(getLogbookForTests(), 'w'));
});

beforeEach(function () {
    Config::set('logbook.channel', 'file');
});
