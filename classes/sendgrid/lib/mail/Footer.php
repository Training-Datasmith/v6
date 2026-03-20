<?php

declare (strict_types=1);
/**
 * This helper builds the Footer object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Footer object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Footer implements \JsonSerializable
{
    /** @var $enable bool Indicates if this setting is enabled */
    private $enable;
    /** @var $text string The plain text content of your footer */
    private $text;
    /** @var $html string The HTML content of your footer */
    private $html;
    /**
     * Optional constructor
     *
     * @param bool|null   $enable Indicates if this setting is enabled
     * @param string|null $text   The plain text content of your footer
     * @param string|null $html   The HTML content of your footer
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($enable = null, $text = null, $html = null)
    {
        if (isset($enable)) {
            $this->set_enable($enable);
        }
        if (isset($text)) {
            $this->set_text($text);
        }
        if (isset($html)) {
            $this->set_html($html);
        }
    }
    /**
     * Update the enable setting on a Footer object
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
     * Retrieve the enable setting on a Footer object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Add text to a Footer object
     *
     * @param string $text The plain text content of your footer
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_text($text): void
    {
        Assert::string($text, 'text');
        $this->text = $text;
    }
    /**
     * Retrieve text to a Footer object
     *
     * @return string
     */
    public function get_text()
    {
        return $this->text;
    }
    /**
     * Add html to a Footer object
     *
     * @param string $html The HTML content of your footer
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_html($html): void
    {
        Assert::string($html, 'html');
        $this->html = $html;
    }
    /**
     * Retrieve html from a Footer object
     *
     * @return string
     */
    public function get_html()
    {
        return $this->html;
    }
    /**
     * Return an array representing a Footer object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable(), 'text' => $this->get_text(), 'html' => $this->get_html()], fn(bool|string $value) => $value !== null) ?: null;
    }
}