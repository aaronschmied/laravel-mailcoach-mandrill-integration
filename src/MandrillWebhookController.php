<?php


namespace SchmiedDev\MailcoachMandrillIntegration;


use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProcessor;

class MandrillWebhookController
{
    public function __invoke(Request $request)
    {
        $webhookConfig = MailgunWebhookConfig::get();

        (new WebhookProcessor($request, $webhookConfig))->process();

        return response()->json(['message' => 'ok']);
    }
}
