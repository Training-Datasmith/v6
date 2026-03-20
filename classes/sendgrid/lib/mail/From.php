<?php

declare (strict_types=1);
/**
 * This helper builds the From object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a From object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class From extends Email_Address implements \JsonSerializable
{
}