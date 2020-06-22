<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Drivers;

use GuzzleHttp\ClientInterface as Http;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Config\Repository;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
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
     */
    public function send(Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        try {
            $response = $this->client->request(
                'POST',
                'messages/send.json',
                [
                    'form_params' => [
                        'key'     => $this->config->get('key', ''),
                        'message' => $this->encodeMessage($message),
                        'ip_pool' => $this->config->get('ip_pool', null),
                        'async'   => false,
                    ],
                ]
            );

            $message->getHeaders()->addTextHeader('X-Mandrill-Message-Id', $this->getMessageId($response));
        }
        catch (TransferException $exception) {
            Log::error('Could not send message', [
                'response' => $exception->getMessage(),
                'code'     => $exception->getCode(),
            ]);

            $failedRecipients = collect($this->getRecipients($message))
                ->pluck('email')
                ->toArray();

            return 0;
        }

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the message id from the response.
     *
     * @param ResponseInterface $response
     *
     * @return string|null
     */
    protected function getMessageId(ResponseInterface $response): ?string
    {
        $responseBody = $response->getBody()->getContents();

        try {
            $responseData = json_decode($responseBody, true);
            return Arr::get($responseData, '0._id');
        }
        catch (\Exception $exception) {
            Log::warning('Could not read message id from mandrill response.');
        }

        return null;
    }

    /**
     * Encode the message.
     *
     * @param Message $message
     *
     * @return array
     */
    protected function encodeMessage(Message $message): array
    {
        $isBodyHtml = $message->getBodyContentType() === 'text/html';

        return [
            'html' => $isBodyHtml ? $message->getBody() : null,
            'text' => $isBodyHtml ? null : $message->getBody(),

            'auto_text' => $isBodyHtml,
            'auto_html' => false,

            'subject' => $message->getSubject(),

            'from_email' => array_key_first($message->getFrom()),
            'from_name'  => $message->getFrom()[array_key_first($message->getFrom())] ?? null,
            'to'         => $this->getRecipients($message),

            'headers' => $this->getMessageHeaders($message),

            'important' => $message->getPriority() > 3,

            'attachments' => $this->getAttachments($message),
            'images'      => $this->getEmbeddedImages($message),

            'track_opens'        => $this->config->get('track_opens', false),
            'track_clicks'       => $this->config->get('track_clicks', false),
            'tracking_domain'    => $this->config->get('tracking_domain', null),
            'return_path_domain' => $this->config->get('return_path_domain', null),
        ];
    }

    /**
     * Get the messages headers as a key value array.
     *
     * @param Message $message
     *
     * @return array
     */
    protected function getMessageHeaders(Message $message): array
    {
        $headers = [];
        foreach ($message->getHeaders()->getAll() as $header) {
            $headers[$header->getFieldName()] = $header->getFieldBody();
        }
        return $headers;
    }


    /**
     * Get the attachments.
     *
     * @param Message $message
     *
     * @return array|null
     */
    protected function getAttachments(Message $message): ?array
    {
        return $this->encodeAttachments(
            collect($message->getChildren())
                ->filter(function ($child) {
                    return $child instanceof \Swift_Attachment;
                })
        );
    }

    /**
     * Get the embedded images.
     *
     * @param Message $message
     *
     * @return array|null
     */
    protected function getEmbeddedImages(Message $message): ?array
    {
        return $this->encodeAttachments(
            collect($message->getChildren())
                ->filter(function ($child) {
                    return $child instanceof \Swift_Image;
                })
        );
    }

    /**
     * Encode the given attachments to the format required by mandrill.
     *
     * @param Collection $attachments
     *
     * @return array|null
     */
    protected function encodeAttachments(Collection $attachments): ?array
    {
        if ($attachments->isEmpty()) {
            return null;
        }

        return $attachments
            ->map(function (\Swift_Mime_Attachment $attachment) {
                return [
                    'type'    => $attachment->getContentType(),
                    'name'    => $attachment->getFilename(),
                    'content' => base64_encode($attachment->getBody()),
                ];
            })
            ->toArray();
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
    protected function getRecipients(Message $message)
    {
        $recipients = [];

        $targets = [
            'to'  => $message->getTo(),
            'cc'  => $message->getCc(),
            'bcc' => $message->getBcc(),
        ];

        foreach ($targets as $type => $targetRecipients) {
            if ($targetRecipients) {
                foreach ($targetRecipients as $email => $name) {
                    $recipients[] = compact('name', 'email', 'type');
                }
            }
        }

        return $recipients;
    }
}
