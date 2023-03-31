<?php

namespace AwStudio\Logbook\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \AwStudio\Logbook\LogEntry request(string $name, string $description)
 * @method static \AwStudio\Logbook\LogEntry modelEvent()
 * @method static \AwStudio\Logbook\LogEntry log(string $name, string $description)
 * @method static \AwStudio\Logbook\Support\AttributeObfuscator getObfuscator()
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
