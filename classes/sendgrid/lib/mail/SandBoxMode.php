<?php

declare(strict_types=1);
/**
 * This helper builds the SandBoxMode object for a /mail/send API call
 */

namespace SendGrid\Mail;

use SendGrid\Helper\Assert;

/**
 * This class is used to construct a SandBoxMode object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class SandBoxMode implements \JsonSerializable
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
            $this->setEnable($enable);
        }
    }

    /**
     * Update the enable setting on a SandBoxMode object
     *
     * @param bool $enable Indicates if this setting is enabled
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function setEnable($enable): void
    {
        Assert::boolean($enable, 'enable');

        $this->enable = $enable;
    }

    /**
     * Retrieve the enable setting on a SandBoxMode object
     *
     * @return bool
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * Return an array representing a SandBoxMode object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_filter(
            [
                'enable' => $this->getEnable(),
            ],
            fn (bool $value) => $value !== null
        ) ?: null;
    }
}
