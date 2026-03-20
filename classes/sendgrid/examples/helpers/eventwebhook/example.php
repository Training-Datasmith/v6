<?php

declare (strict_types=1);
use Send_Grid\Event_Webhook\Event_Webhook;
use Send_Grid\Event_Webhook\Event_Webhook_Header;
function is_valid_signature($request)
{
    $public_key = 'base64-encoded public key';
    $event_webhook = new Event_Webhook();
    $ec_public_key = $event_webhook->convert_public_key_to_ecdsa($public_key);
    return $event_webhook->verify_signature($ec_public_key, $request->get_content(), $request->header(Event_Webhook_Header::SIGNATURE), $request->header(Event_Webhook_Header::TIMESTAMP));
}