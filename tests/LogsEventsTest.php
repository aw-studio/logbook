<?php

use AwStudio\Logbook\Traits\LogsEvents;
use Illuminate\Support\Facades\Config;

test('It logs when a model is created', function () {
    event('eloquent.created: '.TestModel::class, new TestModel(['name' => 'test']));
    expect(latestLogEntry()['type'])->toBe('model_event');
    expect(latestLogEntry()['description'])->toBe('created');
});

test('It logs when a model is updated', function () {
    $model = new TestModel(['name' => 'test']);
    event('eloquent.updated: '.TestModel::class, $model->fill(['name' => 'test2']));
    expect(latestLogEntry()['type'])->toBe('model_event');
    expect(latestLogEntry()['description'])->toBe('updated');
});

test('It logs when a model is deleted', function () {
    $model = new TestModel(['name' => 'test']);
    event('eloquent.deleted: '.TestModel::class, $model);
    expect(latestLogEntry()['type'])->toBe('model_event');
    expect(latestLogEntry()['description'])->toBe('deleted');
});

beforeEach(function () {
    Config::set('logbook.channel', 'file');
});

afterEach(function () {
    fclose(fopen(getLogbookForTests(), 'w'));
});
class TestModel extends \Illuminate\Database\Eloquent\Model
{
    use LogsEvents;

    protected $guarded = [];
}
