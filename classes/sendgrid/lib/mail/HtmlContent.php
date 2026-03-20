<?php

declare (strict_types=1);
/**
 * This helper builds the Content object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a Content object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Html_Content extends Content
{
    /**
     * Create a Content object with a HTML mime type
     *
     * @param string $value HTML formatted content
     *
     * @throws TypeException
     */
    public function __construct($value)
    {
        parent::__construct(Mime_Type::HTML, $value);
    }
}