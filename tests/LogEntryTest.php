<?php

use AwStudio\Logbook\LogEntry;
use Illuminate\Support\Facades\Config;

afterEach(function () {
    fclose(fopen(getLogbookForTests(), 'w'));
});

beforeEach(function () {
    Config::set('logbook.channel', 'file');
});

test('It sets the given payload', function () {
    $logbook = new LogEntry('test');

    $reflectedClass = new \ReflectionClass($logbook);
    $reflection = $reflectedClass->getProperty('payload');
    $reflection->setAccessible(true);

    expect($reflection->getValue($logbook))->toBe('test');
});

test('It sets the given tags', function () {
    $logbook = (new LogEntry('test'))->tag('foo');

    $reflectedClass = new \ReflectionClass($logbook);
    $reflection = $reflectedClass->getProperty('tags');
    $reflection->setAccessible(true);

    expect($reflection->getValue($logbook))->toContain('foo');
});

test('It writes the logEntry to the logbook file when destructed', function () {
    $logbook = new LogEntry('test');
    // this will trigger the __destruct method
    unset($logbook);

    expect(latestLogEntry()['payload'])->toBe([
        'message' => 'test',
    ]);
});
