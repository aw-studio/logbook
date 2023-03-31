<?php

namespace AwStudio\Logbook;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LogEntry
{
    private $payload;

    private $tags = [];

    private $batchName;

    private $batchId;

    private $callerInformation;

    private $location;

    private $loggedAt;

    private $withCallerInformation = true;

    private string $type = 'log';

    private $model_type;

    private $model_id;

    private $description;

    private $logToLocalOnly = false;

    private $copyLocal = false;

    public function __construct($payload)
    {
        $this->payload = $payload;
        $this->loggedAt = Carbon::now();
    }

    public function tag($tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function setBatchName($batchName)
    {
        $this->batchName = $batchName;

        return $this;
    }

    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    public function setCallerInformation($callerInformation)
    {
        $this->callerInformation = $callerInformation;

        $this->setLocation($callerInformation['file'].':'.$callerInformation['line']);

        return $this;
    }

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function description(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function model(Model $model)
    {
        $this->model_type = $model->getMorphClass();
        $this->model_id = $model->getKey();
        $this->setLocation($model->getMorphClass().':'.$model->getKey());

        return $this;
    }

    public function withoutCallerInformation()
    {
        $this->withCallerInformation = false;

        return $this;
    }

    public function ttl(int $ttl)
    {
        // $this->loggedAt = Carbon::now()->subSeconds($ttl);

        return $this;
    }

    public function copyLocal()
    {
        $this->copyLocal = true;

        return $this;
    }

    public function localOnly()
    {
        $this->logToLocalOnly = true;

        return $this;
    }

    public function __destruct()
    {
        if ($this->copyLocal === true) {
            $this->logToFile(config('logbook.log_path'));
        }

        $this->logToFile();
    }

    protected function logToFile($filepath = null)
    {
        $properties = [
            'tags' => $this->tags,
            'batch_name' => $this->batchName,
            'batch_id' => $this->batchId,
            'location' => $this->location,
            'logged_at' => $this->loggedAt,
            'payload' => $this->buildPayload($this->payload),
            'type' => $this->type,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'description' => $this->description,
            'occurrence' => $this->withCallerInformation ? $this->callerInformation : null,
        ];

        $log = json_encode($properties);

        $filepath = $filepath ?? $this->getFilePath();

        file_put_contents($filepath, $log.PHP_EOL, FILE_APPEND);
    }

    protected function getFilePath()
    {
        if ($this->logToLocalOnly === true) {
            return config('logbook.log_path');
        }

        if (config('logbook.channel') === 'api') {
            return (new Logbook)->getLogPathForTempApiLog();
        }

        return config('logbook.log_path');
    }

    protected function buildPayload($payload)
    {
        if (! is_array($payload)) {
            $payload = ['message' => $payload];
        }

        return $payload;
    }
}
