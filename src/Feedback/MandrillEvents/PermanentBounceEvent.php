<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;

class PermanentBounceEvent extends MandrillEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'hard_bounce' || $this->event === 'reject';
    }

    public function handle(Send $send)
    {
        $send->registerBounce($this->getTimestamp());
    }
}
