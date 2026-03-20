<?php

declare (strict_types=1);
/**
 * This helper builds the BypassUnsubscribeManagement object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a BypassUnsubscribeManagement object for
 * the /mail/send API call
 *
 * Allows you to bypass the global unsubscribe list to ensure that the email is delivered
 * to recipients. Bounce and spam report lists will still be checked; addresses on these
 * other lists will not receive the message. This filter applies only to global unsubscribes
 * and will not bypass group unsubscribes.
 *
 * This filter cannot be combined with the bypass_list_management filter.
 *
 * @package SendGrid\Mail
 */
class Bypass_Unsubscribe_Management implements \JsonSerializable
{
    /** @var $enable bool Indicates if this setting is enabled */
    private $enable;
    /**
     * Optional constructor
     *
     * @param bool|null $enable Indicates if this setting is enabled
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($enable = null)
    {
        if (isset($enable)) {
            $this->set_enable($enable);
        }
    }
    /**
     * Update the enable setting on a BypassUnsubscribeManagement object
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
     * Retrieve the enable setting on a BypassUnsubscribeManagement object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Return an array representing a BypassUnsubscribeManagement object for
     * the SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable()], fn(bool $value) => $value !== null) ?: null;
    }
}