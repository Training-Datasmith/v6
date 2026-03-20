<?php

declare (strict_types=1);
/**
 * This helper builds the BypassListManagement object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a BypassListManagement object for
 * the /mail/send API call
 *
 * Allows you to bypass all unsubscribe groups and suppressions to
 * ensure that the email is delivered to every single recipient. This
 * should only be used in emergencies when it is absolutely necessary
 * that every recipient receives your email
 *
 * @package SendGrid\Mail
 */
class Bypass_List_Management implements \JsonSerializable
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
     * Update the enable setting on a BypassListManagement object
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
     * Retrieve the enable setting on a BypassListManagement object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Return an array representing a BypassListManagement object for
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