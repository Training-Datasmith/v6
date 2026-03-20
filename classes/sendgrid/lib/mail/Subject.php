<?php

declare (strict_types=1);
/**
 * This helper builds the Subject object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Subject object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Subject implements \JsonSerializable
{
    /** @var $subject string The email subject */
    private $subject;
    /**
     * Optional constructor
     *
     * @param string|null $subject The email subject
     *
     * @throws TypeException
     */
    public function __construct($subject = null)
    {
        if (isset($subject)) {
            $this->set_subject($subject);
        }
    }
    /**
     * Set the subject on a Subject object
     *
     * @param string $subject The email subject
     *
     * @throws TypeException
     */
    public function set_subject($subject): void
    {
        Assert::min_length($subject, 'subject', 1);
        $this->subject = $subject;
    }
    /**
     * Retrieve the subject from a Subject object
     */
    public function get_subject(): string
    {
        return mb_convert_encoding((string) $this->subject, 'UTF-8', 'UTF-8');
    }
    /**
     * Return an array representing a Subject object for the Twilio SendGrid API
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return $this->get_subject();
    }
}