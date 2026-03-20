<?php

declare (strict_types=1);
/**
 * This helper builds the ReplyTo object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a ReplyTo object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Reply_To extends Email_Address implements \JsonSerializable
{
}