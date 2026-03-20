<?php

declare (strict_types=1);
/**
 * This helper builds the Section object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Section object for the /mail/send API call
 *
 * An object of key/value pairs that define block sections of code to be used
 * as substitutions
 *
 * @package SendGrid\Mail
 */
class Section implements \JsonSerializable
{
    /** @var $key string Section key */
    private $key;
    /** @var $value string Section value */
    private $value;
    /**
     * Optional constructor
     *
     * @param string|null $key   Section key
     * @param string|null $value Section value
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
     * Add the key on a Section object
     *
     * @param string $key Section key
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_key($key): void
    {
        Assert::string($key, 'key');
        $this->key = $key;
    }
    /**
     * Retrieve the key from a Section object
     *
     * @return string
     */
    public function get_key()
    {
        return $this->key;
    }
    /**
     * Add the value on a Section object
     *
     * @param string $value Section value
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_value($value): void
    {
        Assert::string($value, 'value');
        $this->value = $value;
    }
    /**
     * Retrieve the value from a Section object
     *
     * @return string
     */
    public function get_value()
    {
        return $this->value;
    }
    /**
     * Return an array representing a Section object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['key' => $this->get_key(), 'value' => $this->get_value()], fn(string $value) => $value !== null) ?: null;
    }
}