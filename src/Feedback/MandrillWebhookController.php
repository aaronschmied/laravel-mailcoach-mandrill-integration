<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback;

use Illuminate\Http\Request;

class MandrillWebhookController
{
    public function __invoke(Request $request)
    {
        return (new MandrillWebhookProcessor(
            $request,
            MandrillWebhookConfig::get()
        ))->process();
    }
}
