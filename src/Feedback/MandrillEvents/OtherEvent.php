<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents;

use Spatie\Mailcoach\Models\Send;

class OtherEvent extends MandrillEvent
{
    public function canHandlePayload(): bool
    {
        return true;
    }

    public function handle(Send $send)
    {
    }
}
