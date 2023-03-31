<?php

namespace AwStudio\Logbook\Traits;

use AwStudio\Logbook\Facades\Logbook;
use Illuminate\Support\Collection;

trait LogsEvents
{
    protected static function bootLogsEvents(): void
    {
        static::eventsToBeLogged()->each(function ($eventName) {
            static::$eventName(function ($model) use ($eventName) {
                Logbook::modelEvent($model, $eventName, $model->getChangesForLog());
            });
        });
    }

    /**
     * Obfuscate or hide attributes within model's changes before logging.
     */
    protected function getChangesForLog(): array
    {
        $params = Logbook::getObfuscator()->obfuscate(
            $this->getChanges(),
            $this->getAttributesToObfuscate()
        );

        return Logbook::getObfuscator()->hide(
            $params,
            $this->getAttributesToHide()
        );
    }

    /**
     * Get the attributes that should be hidden from the log.
     */
    protected function getAttributesToHide(): array
    {
        return array_merge(config('logbook.hidden_fields'), $this->getHidden());
    }

    /**
     * Get the attributes that should be obfuscated from the log.
     */
    protected function getAttributesToObfuscate(): array
    {
        return config('logbook.obfuscated_fields');
    }

    protected static function eventsToBeLogged(): Collection
    {
        if (isset(static::$loggedEvents)) {
            return collect(static::$loggedEvents);
        }

        $events = collect([
            'created',
            'updated',
            'deleted',
        ]);

        if (collect(class_uses_recursive(static::class))->contains(SoftDeletes::class)) {
            $events->push('restored');
        }

        return $events;
    }
}
