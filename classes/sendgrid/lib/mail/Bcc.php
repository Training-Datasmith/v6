<?php

declare (strict_types=1);
/**
 * This helper builds the Bcc object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a Bcc object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Bcc extends Email_Address implements \JsonSerializable
{
}