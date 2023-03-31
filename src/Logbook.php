<?php

namespace AwStudio\Logbook;

use Illuminate\Support\Facades\Http;
use Throwable;

class Logbook
{
    use Concerns\LogsToApi;
    use Concerns\ObfuscatesLogContent;

    /**
     * @var \Illuminate\Http\Client
     */
    protected $client;

    /**
     * @var LogBatch
     */
    protected $batch;

    public function __construct()
    {
        if (config('logbook.channel') === 'api') {
            $this->checkApiChannelConfiguration();

            $this->client = Http::baseUrl(config('logbook.api_endpoint'));
        }

        $this->batch = new LogBatch();
    }

    public function __destruct()
    {
        if (config('logbook.channel') === 'api') {
            $this->sendLogsToApi();
        }
    }

    public function open(?string $name = null)
    {
        $this->batch->start($name);
    }

    public function close()
    {
        $this->batch->end();
    }

    public function getBatchUuid()
    {
        return $this->batch->getUuid();
    }

    public function request(...$properties)
    {
        return $this->log(
            [
                'request' => [
                    'headers' => request()->headers->all(),
                    'request_parames' => $this->getObfuscatedRequestParams(),
                ],
                ...$properties,
            ]
        );
    }

    public function exception(Throwable $exception)
    {
        $this->log([
            'headers' => request()->headers->all(),
            'request_params' => $this->getObfuscatedRequestParams(),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'code' => (int) $exception->getCode(),
            'file' => $exception->getFile().':'.$exception->getLine(),
        ])
        ->type('exception')
        ->setLocation(str_replace(base_path(), '', $exception->getFile()).':'.$exception->getLine());

        return $this;
    }

    public function modelEvent(
        \Illuminate\Database\Eloquent\Model $model,
        string $event,
        array $properties = [])
    {
        return $this->log($properties)
                ->model($model)
                ->description($event)
                ->withoutCallerInformation()
                ->type('model_event');
    }

    public function log(mixed $payload): LogEntry
    {
        return (new LogEntry($payload))
                ->setBatchName($this->batch->getName())
                ->setBatchId($this->batch->getUuid())
                ->setCallerInformation($this->getCallerInformation());
    }

    public function getCallerInformation()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        // The information which file, line number, class and method that called the method
        // are spread over 2 entries of the backtrace. Therefore we
        // need to loop through the backtrace and find the first entry
        // that is not from this class or the Facade class, to find the "caller"
        // and then get the previous entry to get the information we need.

        // If the third entry in the trace is not from this class or a Facade class,
        // then it must be the "caller". Otherwise, we go up the stack until we find
        // a non-matching entry.
        for ($i = 0; isset($trace[$i]); $i++) {
            if (! isset($trace[$i]['class'])) {
                continue;
            }

            $callerClass = $trace[$i]['class'];
            if ($callerClass === self::class || $callerClass === 'Illuminate\Support\Facades\Facade') {
                continue;
            }

            $previousTrace = $trace[($i - 1)];

            return [
                'class' => $callerClass,
                'function' => $trace[$i]['function'],
                'args' => $previousTrace['args'],
                'file' => str_replace(base_path(), '', $previousTrace['file']),
                'line' => $previousTrace['line'],
            ];
        }

        // If we got here, then we couldn't find a caller outside of this class or Facade class.
        return null;
    }

    public function getBatch(): LogBatch
    {
        return $this->batch;
    }
}
