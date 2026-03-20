<?php

declare (strict_types=1);
/**
 * This helper builds the Header object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Header object for the /mail/send API call
 *
 * An object containing key/value pairs of header names and the value to substitute
 * for them. You must ensure these are properly encoded if they contain unicode
 * characters. Must not be one of the reserved headers
 *
 * @package SendGrid\Mail
 */
class Header implements \JsonSerializable
{
    /** @var $key string Header key */
    private $key;
    /** @var $value string Header value */
    private $value;
    /**
     * Optional constructor
     *
     * @param string|null $key   Header key
     * @param string|null $value Header value
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($key = null, $value = null)
    {
        if (isset($key)) {
            $this->set_key($key);
        }
        if (isset($value)) {
            $this->set_value($value);
        }
    }
    /**
     * Add the key on a Header object
     *
     * @param string $key Header key
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_key($key): void
    {
        Assert::string($key, 'key');
        $this->key = $key;
    }
    /**
     * Retrieve the key from a Header object
     *
     * @return string
     */
    public function get_key()
    {
        return $this->key;
    }
    /**
     * Add the value on a Header object
     *
     * @param string $value Header value
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_value($value): void
    {
        Assert::string($value, 'value');
        $this->value = $value;
    }
    /**
     * Retrieve the value from a Header object
     *
     * @return string
     */
    public function get_value()
    {
        return $this->value;
    }
    /**
     * Return an array representing a Header object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['key' => $this->get_key(), 'value' => $this->get_value()], fn(string $value) => $value !== null) ?: null;
    }
}