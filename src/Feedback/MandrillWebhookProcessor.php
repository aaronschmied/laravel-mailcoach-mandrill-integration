<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback;

use Spatie\WebhookClient\WebhookProcessor;

class MandrillWebhookProcessor extends WebhookProcessor
{
    public function process()
    {
        $this->ensureValidSignature();

        if (!$this->config->webhookProfile->shouldProcess($this->request)) {
            return $this->createResponse();
        }

        foreach (json_decode($this->request->mandrill_events, true) ?? [] as $event) {
            $call = $this
                ->config
                ->webhookModel::create([
                                           'name'    => $this->config->name,
                                           'payload' => $event,
                                       ]);

            $this->processWebhook($call);
        }

        return $this->createResponse();
    }
}
