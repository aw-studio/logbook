<?php

namespace AwStudio\Logbook\Concerns;

trait ObfuscatesLogContent
{
    public static function getObfuscator()
    {
        $obfuscator = config('logbook.obfuscator_class', AttributeObfuscator::class);

        return new $obfuscator;
    }

    protected function getObfuscatedRequestParams()
    {
        $params = request()->all();

        $obfuscator = self::getObfuscator();

        $params = $obfuscator->obfuscate(
            $params,
            config('logbook.obfuscated_fields')
        );

        $params = $obfuscator->hide(
            $params,
            config('logbook.hidden_fields')
        );

        return $params;
    }
}
