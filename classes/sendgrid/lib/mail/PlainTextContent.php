<?php

declare (strict_types=1);
/**
 * This helper builds theContent object for a /mail/send API call
 */
namespace Send_Grid\Mail;

/**
 * This class is used to construct a Content object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Plain_Text_Content extends Content
{
    /**
     * Create a Content object with a plain text mime type
     *
     * @param string $value plain text formatted content
     *
     * @throws TypeException
     */
    public function __construct($value)
    {
        parent::__construct(Mime_Type::TEXT, $value);
    }
}