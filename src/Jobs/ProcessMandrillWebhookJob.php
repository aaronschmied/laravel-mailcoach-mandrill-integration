<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Jobs;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use SchmiedDev\MailcoachMandrillIntegration\Feedback\MandrillEventFactory;
use Spatie\Mailcoach\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Models\Send;
use Spatie\Mailcoach\Support\Config;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessMandrillWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.perform_on_queue.process_feedback_job');

        $this->connection = $this->connection ?? Config::getQueueConnection();
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        Log::info('Handle webhook call.', $payload);

        if (!$send = $this->getSend()) {
            return;
        };

        $mandrillEvent = MandrillEventFactory::createForPayload($payload);

        $mandrillEvent->handle($send);

        event(new WebhookCallProcessedEvent($this->webhookCall));
    }

    protected function getSend(): ?Send
    {
        $messageId = Arr::get($this->webhookCall->payload, 'msg._id');

        if (!$messageId) {
            return null;
        }

        return Send::findByTransportMessageId($messageId);
    }
}
