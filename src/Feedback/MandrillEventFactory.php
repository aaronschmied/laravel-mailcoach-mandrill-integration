<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback;

use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents\ClickEvent;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents\MandrillEvent;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents\OpenEvent;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents\OtherEvent;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents\PermanentBounceEvent;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents\SpamEvent;

class MandrillEventFactory
{
    protected static array $events = [
        ClickEvent::class,
        SpamEvent::class,
        OpenEvent::class,
        PermanentBounceEvent::class,
    ];

    public static function createForPayload(array $payload): MandrillEvent
    {
        $event = collect(static::$events)
            ->map(fn (string $eventClass) => new $eventClass($payload))
            ->first(fn (MandrillEvent $event) => $event->canHandlePayload());

        return $sendgridEvent ?? new OtherEvent($payload);
    }
}
