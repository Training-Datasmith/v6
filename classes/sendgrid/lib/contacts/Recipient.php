<?php

declare(strict_types=1);
/**
 * This helper builds a recipient for a /mail/send API call
 */

namespace SendGrid\Contacts;

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
    public function __construct(private $firstName, private $lastName, private $email)
    {
    }

    /**
     * Retrieve the first name of the recipient
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Retrieve the last name of the recipient
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Retrieve the email address of the recipient
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return an array representing a recipient object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_filter(
            [
                'email' => $this->getEmail(),
                'first_name' => $this->getFirstName(),
                'last_name' => $this->getLastName(),
            ],
            fn (string $value) => $value !== null
        ) ?: null;
    }
}
