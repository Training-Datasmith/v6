<?php

declare (strict_types=1);
/**
 * This helper builds the ClickTracking object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a ClickTracking object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Click_Tracking implements \JsonSerializable
{
    /** @var $enable bool Indicates if this setting is enabled */
    private $enable;
    /* @var $enable_text bool Indicates if this setting should be included in the text/plain portion of your email */
    private $enable_text;
    /**
     * Optional constructor
     *
     * @param bool|null $enable      Indicates if this setting is enabled
     * @param bool|null $enable_text Indicates if this setting should be
     *                               included in the text/plain portion of
     *                               your email
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($enable = null, $enable_text = null)
    {
        if (isset($enable)) {
            $this->set_enable($enable);
        }
        if (isset($enable_text)) {
            $this->set_enable_text($enable_text);
        }
    }
    /**
     * Update the enable setting on a ClickTracking object
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
     * Retrieve the enable setting on a ClickTracking object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Update the enable text setting on a ClickTracking object
     *
     * @param bool $enable_text Indicates if this setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_enable_text($enable_text): void
    {
        Assert::boolean($enable_text, 'enable_text');
        $this->enable_text = $enable_text;
    }
    /**
     * Retrieve the enable_text setting on a ClickTracking object
     *
     * @return bool
     */
    public function get_enable_text()
    {
        return $this->enable_text;
    }
    /**
     * Return an array representing a ClickTracking object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable(), 'enable_text' => $this->get_enable_text()], fn(bool $value) => $value !== null) ?: null;
    }
}