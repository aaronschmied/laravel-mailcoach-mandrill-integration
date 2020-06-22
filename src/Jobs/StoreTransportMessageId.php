<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Jobs;

use Illuminate\Mail\Events\MessageSent;

class StoreTransportMessageId
{
    public function handle(MessageSent $event)
    {
        if (!isset($event->data['send'])) {
            return;
        }

        if (!$event->message->getHeaders()->has('X-Mandrill-Message-Id')) {
            return;
        }

        /** @var \Spatie\Mailcoach\Models\Send $send */
        $send = $event->data['send'];

        $transportMessageId = $event->message->getHeaders()->get('X-Mandrill-Message-Id')->getFieldBody();

        $send->storeTransportMessageId($transportMessageId);
    }
}
