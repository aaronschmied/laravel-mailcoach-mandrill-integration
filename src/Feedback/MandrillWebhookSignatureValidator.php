<?php

namespace SchmiedDev\MailcoachMandrillIntegration\Feedback;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class MandrillWebhookSignatureValidator implements SignatureValidator
{
    /**
     * Get the requests content for the signature validation.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getRequestPayloadForSigning(Request $request)
    {
        $url = route('mandrillFeedback');

        $signedData = $url;

        $params = $request->toArray();

        ksort($params);

        foreach ($params as $key => $value) {
            $signedData .= $key;
            $signedData .= $value;
        }

        return $signedData;
    }

    /**
     * Validate the signature for the given request.
     *
     * @param Request       $request
     * @param WebhookConfig $config
     *
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $requestSignature = (string) $request->header($config->signatureHeaderName);

        $generatedSignature = base64_encode(
            hash_hmac(
                'sha1',
                $this->getRequestPayloadForSigning($request),
                $config->signingSecret,
                true
            )
        );

        return hash_equals($generatedSignature, $requestSignature);
    }
}
