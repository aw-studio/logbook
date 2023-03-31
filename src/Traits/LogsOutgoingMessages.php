<?php

namespace AwStudio\Logbook\Traits;

use AwStudio\Logbook\Facades\Logbook;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;

trait LogsOutgoingMessages
{
    public function logOutgoingMessages(): void
    {
        Event::listen(MessageSent::class, function ($event) {
            if (config('logbook.channel') === 'api') {
                Logbook::log($this->apiLogContent($event))
                    ->setType('event')
                    ->setDescription('Message sent');
            }
            Logbook::log($this->localLogContent($event))
                ->setType('event')
                ->setDescription('Message sent')
                ->localOnly();
        });
    }

    public function apiLogContent($event)
    {
        return [
            'subject' => $event->data['message']->getSubject(),
            'messageID' => $event->sent->getMessageId(),
        ];
    }

    public function localLogContent($event)
    {
        return [
            'messageID' => $event->sent->getMessageId(),
            'subject' => $event->data['message']->getSubject(),
            'to' => $event->data['message']->getTo(),
        ];
    }
}
