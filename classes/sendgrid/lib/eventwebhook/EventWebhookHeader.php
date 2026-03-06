<?php

declare(strict_types=1);

namespace SendGrid\EventWebhook;

/**
 * This class lists headers that get posted to the webhook. Read the docs for
 * more details: https://sendgrid.com/docs/for-developers/tracking-events/event
 *
 * @package SendGrid\EventWebhook
 */
final class EventWebhookHeader
{
    public const SIGNATURE = 'X-Twilio-Email-Event-Webhook-Signature';
    public const TIMESTAMP = 'X-Twilio-Email-Event-Webhook-Timestamp';
}
