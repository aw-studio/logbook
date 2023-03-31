<?php

namespace AwStudio\Logbook\Facades;

use AwStudio\Logbook\LogEntry;
use AwStudio\Logbook\Support\ObfuscatorInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static LogEntry request()
 * @method static LogEntry modelEvent()
 * @method static LogEntry log()
 * @method static LogEntry exception()
 * @method static ObfuscatorInterface getObfuscator()
 *
 * @see \AwStudio\Logbook\Logbook
 */
class Logbook extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'logbook';
    }
}
