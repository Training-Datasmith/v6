<?php

declare (strict_types=1);
namespace Send_Grid\Event_Webhook;

use Elliptic_Curve\Ecdsa;
use Elliptic_Curve\Public_Key;
use Elliptic_Curve\Signature;
/**
 * This class allows you to use the Event Webhook feature. Read the docs for
 * more details: https://sendgrid.com/docs/for-developers/tracking-events/event
 *
 * @package SendGrid\EventWebhook
 */
class Event_Webhook
{
    /**
     * Convert the public key string to a ECPublicKey.
     *
     * @param string $publicKey verification key under Mail Settings
     * @return PublicKey public key using the ECDSA algorithm
     */
    public function convert_public_key_to_ecdsa($public_key)
    {
        return Public_Key::from_string($public_key);
    }
    /**
     * Verify signed event webhook requests.
     *
     * @param PublicKey $publicKey elliptic curve public key
     * @param string $payload event payload in the request body
     * @param string $signature value obtained from the
     *                         'X-Twilio-Email-Event-Webhook-Signature' header
     * @param string $timestamp value obtained from the
     *                         'X-Twilio-Email-Event-Webhook-Timestamp' header
     * @return bool true or false if signature is valid
     */
    public function verify_signature($public_key, string $payload, $signature, string $timestamp)
    {
        $timestamped_payload = $timestamp . $payload;
        $decoded_signature = Signature::from_base64($signature);
        return Ecdsa::verify($timestamped_payload, $decoded_signature, $public_key);
    }
}