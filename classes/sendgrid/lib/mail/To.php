<?php

declare (strict_types=1);
/**
 * This helper builds the To object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a To object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class To extends Email_Address implements \JsonSerializable
{
}