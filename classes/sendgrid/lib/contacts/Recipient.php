<?php

declare (strict_types=1);
/**
 * This helper builds a recipient for a /mail/send API call
 */
namespace Send_Grid\Contacts;

/**
 * This class is used to construct a recipient for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Recipient implements \JsonSerializable
{
    /**
     * Create a recipient for the /mail/send API call
     *
     * @param string $firstName First name of the email recipient
     * @param string $lastName Last name of the email recipient
     * @param string $email Email address of the recipient
     */
    public function __construct(private $first_name, private $last_name, private $email)
    {
    }
    /**
     * Retrieve the first name of the recipient
     *
     * @return string
     */
    public function get_first_name()
    {
        return $this->first_name;
    }
    /**
     * Retrieve the last name of the recipient
     *
     * @return string
     */
    public function get_last_name()
    {
        return $this->last_name;
    }
    /**
     * Retrieve the email address of the recipient
     *
     * @return string
     */
    public function get_email()
    {
        return $this->email;
    }
    /**
     * Return an array representing a recipient object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['email' => $this->get_email(), 'first_name' => $this->get_first_name(), 'last_name' => $this->get_last_name()], fn(string $value) => $value !== null) ?: null;
    }
}