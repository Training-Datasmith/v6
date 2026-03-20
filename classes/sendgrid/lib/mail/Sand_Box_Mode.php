<?php

declare (strict_types=1);
/**
 * This helper builds the SandBoxMode object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a SandBoxMode object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Sand_Box_Mode implements \JsonSerializable
{
    /**
     * @var bool Indicates if this setting is enabled
     */
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
     * Update the enable setting on a SandBoxMode object
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
     * Retrieve the enable setting on a SandBoxMode object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Return an array representing a SandBoxMode object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable()], fn(bool $value) => $value !== null) ?: null;
    }
}