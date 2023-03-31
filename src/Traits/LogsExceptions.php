<?php

namespace AwStudio\Logbook\Traits;

use AwStudio\Logbook\Facades\Logbook;
use Throwable;

trait LogsExceptions
{
    /**
     * A list of the exception types that should not be reported
     * but should be logged with Logbook.
     */
    protected $shouldLog = [
        \Illuminate\Validation\ValidationException::class,
    ];

    public function shouldntReport(Throwable $e)
    {
        if (in_array(get_class($e), $this->shouldLog)) {
            Logbook::exception($e);
        }

        return parent::shouldntReport($e);
    }
}
