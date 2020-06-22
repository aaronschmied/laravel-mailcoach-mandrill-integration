<?php


namespace SchmiedDev\MailcoachMandrillIntegration\Feedback;


use SchmiedDev\MailcoachMandrillIntegration\Jobs\ProcessMandrillWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class MandrillWebhookConfig
{
    public static function get(): WebhookConfig
    {
        return new WebhookConfig([
             'name'                  => 'mandrill-feedback',
             'signing_secret'        => config('services.mandrill.webhook_signing_key', null),
             'signature_header_name' => 'x-mandrill-signature',
             'signature_validator'   => MandrillWebhookSignatureValidator::class,
             'webhook_profile'       => ProcessEverythingWebhookProfile::class,
             'webhook_model'         => WebhookCall::class,
             'process_webhook_job'   => ProcessMandrillWebhookJob::class,
         ]);
    }

}
