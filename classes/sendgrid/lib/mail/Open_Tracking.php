<?php

declare (strict_types=1);
/**
 * This helper builds the OpenTracking object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a OpenTracking object for the /mail/send API call
 *
 * Allows you to track whether the email was opened or not, but including a single
 * pixel image in the body of the content. When the pixel is loaded, we can log that
 * the email was opened
 *
 * @package SendGrid\Mail
 */
class Open_Tracking implements \JsonSerializable
{
    /** @var $enable bool Indicates if this setting is enabled */
    private $enable;
    /**
     * @var $substitution_tag string Allows you to specify a substitution tag that you can insert in the body of
     *                               your email at a location that you desire. This tag will be replaced by the
     *                               open tracking pixel
     */
    private $substitution_tag;
    /**
     * Optional constructor
     *
     * @param bool|null   $enable           Indicates if this setting is enabled
     * @param string|null $substitution_tag Allows you to specify a substitution
     *                                      tag that you can insert in the body
     *                                      of your email at a location that you
     *                                      desire. This tag will be replaced by
     *                                      the open tracking pixel
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($enable = null, $substitution_tag = null)
    {
        if (isset($enable)) {
            $this->set_enable($enable);
        }
        if (isset($substitution_tag)) {
            $this->set_substitution_tag($substitution_tag);
        }
    }
    /**
     * Update the enable setting on a OpenTracking object
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
     * Retrieve the enable setting on a OpenTracking object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Set the substitution tag on a OpenTracking object
     *
     * @param string $substitution_tag Allows you to specify a substitution
     *                                 tag that you can insert in the body
     *                                 of your email at a location that you
     *                                 desire. This tag will be replaced by
     *                                 the open tracking pixel
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_substitution_tag($substitution_tag): void
    {
        Assert::string($substitution_tag, 'substitution_tag');
        $this->substitution_tag = $substitution_tag;
    }
    /**
     * Retrieve the substitution tag from a OpenTracking object
     *
     * @return string
     */
    public function get_substitution_tag()
    {
        return $this->substitution_tag;
    }
    /**
     * Return an array representing a OpenTracking object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable(), 'substitution_tag' => $this->get_substitution_tag()], fn(bool|string $value) => $value !== null) ?: null;
    }
}