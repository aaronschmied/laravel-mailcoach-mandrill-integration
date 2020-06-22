<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents;

use Spatie\Mailcoach\Models\Send;

class OpenEvent extends MandrillEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'open';
    }

    public function handle(Send $send)
    {
        return $send->registerOpen($this->getTimestamp());
    }
}
