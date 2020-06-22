<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents;

use Spatie\Mailcoach\Models\Send;

class SpamEvent extends MandrillEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'spamreport';
    }

    public function handle(Send $send)
    {
        $send->registerComplaint($this->getTimestamp());
    }
}
