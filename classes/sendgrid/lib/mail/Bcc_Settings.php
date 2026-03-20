<?php

declare (strict_types=1);
/**
 * This helper builds the BccSettings object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a BccSettings object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Bcc_Settings implements \JsonSerializable
{
    /** @var $enable bool Indicates if this setting is enabled */
    private $enable;
    /** @var $email string The email address that you would like to receive the BCC */
    private $email;
    /**
     * Optional constructor
     *
     * @param bool|null   $enable Indicates if this setting is enabled
     * @param string|null $email  The email address that you would like
     *                            to receive the BCC
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($enable = null, $email = null)
    {
        if (isset($enable)) {
            $this->set_enable($enable);
        }
        if (isset($email)) {
            $this->set_email($email);
        }
    }
    /**
     * Update the enable setting on a BccSettings object
     *
     * @param bool $enable Indicates if this setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_enable($enable): void
    {
        Assert::boolean($enable, 'enable');
        $this->enable = $enable;
    }
    /**
     * Retrieve the enable setting on a BccSettings object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Add the email setting on a BccSettings object
     *
     * @param string $email The email address that you would like
     *                      to receive the BCC
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_email($email): void
    {
        Assert::email($email, 'email');
        $this->email = $email;
    }
    /**
     * Retrieve the email setting on a BccSettings object
     *
     * @return string
     */
    public function get_email()
    {
        return $this->email;
    }
    /**
     * Return an array representing a BccSettings object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable(), 'email' => $this->get_email()], static fn(bool|string $value) => $value !== null) ?: null;
    }
}