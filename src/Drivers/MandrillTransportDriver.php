<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Drivers;

use GuzzleHttp\ClientInterface as Http;
use Illuminate\Config\Repository;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage as Message;

class MandrillTransportDriver extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var Http
     */
    protected Http $client;

    /**
     * The driver config.
     *
     * @var Repository
     */
    protected Repository $config;

    /**
     * Create a new Mandrill transport instance.
     *
     * @param Http       $client
     * @param Repository $config
     *
     * @return void
     */
    public function __construct(Http $client, Repository $config)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * This is the responsibility of the send method to start the transport if needed.
     *
     * @param Message  $message
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $this->client->request('POST', 'https://mandrillapp.com/api/1.0/messages/send-raw.json', [
            'form_params' => [
                'key'         => $this->config->get('key'),
                'to'          => $this->getTo($message),
                'raw_message' => $message->toString(),
                'async'       => true,
            ],
        ]);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get all the addresses this message should be sent to.
     *
     * Note that Mandrill still respects CC, BCC headers in raw message itself.
     *
     * @param Message $message
     *
     * @return array
     */
    protected function getTo(Message $message)
    {
        $to = [];

        if ($message->getTo()) {
            $to = array_merge($to, array_keys($message->getTo()));
        }

        if ($message->getCc()) {
            $to = array_merge($to, array_keys($message->getCc()));
        }

        if ($message->getBcc()) {
            $to = array_merge($to, array_keys($message->getBcc()));
        }

        return $to;
    }
}
