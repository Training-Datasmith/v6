<?php

declare (strict_types=1);
/**
 * This helper defines the content mime types for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to define the content mime types for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
final class Mime_Type
{
    public const HTML = 'text/html';
    public const TEXT = 'text/plain';
}