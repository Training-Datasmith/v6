<?php

declare (strict_types=1);
/**
 * This helper builds the Cc object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a Cc object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Cc extends Email_Address implements \JsonSerializable
{
}