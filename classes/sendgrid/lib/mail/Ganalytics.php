<?php

declare (strict_types=1);
/**
 * This helper builds the Ganalytics object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a Ganalytics object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Ganalytics implements \JsonSerializable
{
    /** @var $enable bool Indicates if this setting is enabled */
    private $enable;
    /** @var $utm_source string Name of the referrer source. (e.g. Google, SomeDomain.com, or Marketing Email) */
    private $utm_source;
    /** @var $utm_medium string Name of the marketing medium. (e.g. Email) */
    private $utm_medium;
    /** @var $utm_term string Used to identify any paid keywords */
    private $utm_term;
    /** @var $utm_content string Used to differentiate your campaign from advertisements */
    private $utm_content;
    /** @var $utm_campaign string The name of the campaign */
    private $utm_campaign;
    /**
     * Optional constructor
     *
     * @param bool|null   $enable       Indicates if this setting is enabled
     * @param string|null $utm_source   Name of the referrer source. (e.g.
     *                                  Google, SomeDomain.com, or Marketing Email)
     * @param string|null $utm_medium   Name of the marketing medium. (e.g. Email)
     * @param string|null $utm_term     Used to identify any paid keywords
     * @param string|null $utm_content  Used to differentiate your campaign from
     *                                  advertisements
     * @param string|null $utm_campaign The name of the campaign
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($enable = null, $utm_source = null, $utm_medium = null, $utm_term = null, $utm_content = null, $utm_campaign = null)
    {
        if (isset($enable)) {
            $this->set_enable($enable);
        }
        if (isset($utm_source)) {
            $this->set_campaign_source($utm_source);
        }
        if (isset($utm_medium)) {
            $this->set_campaign_medium($utm_medium);
        }
        if (isset($utm_term)) {
            $this->set_campaign_term($utm_term);
        }
        if (isset($utm_content)) {
            $this->set_campaign_content($utm_content);
        }
        if (isset($utm_campaign)) {
            $this->set_campaign_name($utm_campaign);
        }
    }
    /**
     * Update the enable setting on a Ganalytics object
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
     * Retrieve the enable setting on a Ganalytics object
     *
     * @return bool
     */
    public function get_enable()
    {
        return $this->enable;
    }
    /**
     * Add the campaign source to a Ganalytics object
     *
     * @param string $utm_source Name of the referrer source. (e.g.
     *                           Google, SomeDomain.com, or Marketing Email)
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_campaign_source($utm_source): void
    {
        Assert::string($utm_source, 'utm_source');
        $this->utm_source = $utm_source;
    }
    /**
     * Return the campaign source from a Ganalytics object
     *
     * @return string
     */
    public function get_campaign_source()
    {
        return $this->utm_source;
    }
    /**
     * Add the campaign medium to a Ganalytics object
     *
     * @param string $utm_medium Name of the marketing medium. (e.g. Email)
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_campaign_medium($utm_medium): void
    {
        Assert::string($utm_medium, 'utm_medium');
        $this->utm_medium = $utm_medium;
    }
    /**
     * Return the campaign medium from a Ganalytics object
     *
     * @return string
     */
    public function get_campaign_medium()
    {
        return $this->utm_medium;
    }
    /**
     * Add the campaign term to a Ganalytics object
     *
     * @param string $utm_term Used to identify any paid keywords
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_campaign_term($utm_term): void
    {
        Assert::string($utm_term, 'utm_term');
        $this->utm_term = $utm_term;
    }
    /**
     * Return the campaign term from a Ganalytics object
     *
     * @return string
     */
    public function get_campaign_term()
    {
        return $this->utm_term;
    }
    /**
     * Add the campaign content to a Ganalytics object
     *
     * @param string $utm_content Used to differentiate your campaign from
     *                            advertisements
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_campaign_content($utm_content): void
    {
        Assert::string($utm_content, 'utm_content');
        $this->utm_content = $utm_content;
    }
    /**
     * Return the campaign content from a Ganalytics object
     *
     * @return string
     */
    public function get_campaign_content()
    {
        return $this->utm_content;
    }
    /**
     * Add the campaign name to a Ganalytics object
     *
     * @param string $utm_campaign The name of the campaign
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_campaign_name($utm_campaign): void
    {
        Assert::string($utm_campaign, 'utm_campaign');
        $this->utm_campaign = $utm_campaign;
    }
    /**
     * Return the campaign name from a Ganalytics object
     *
     * @return string
     */
    public function get_campaign_name()
    {
        return $this->utm_campaign;
    }
    /**
     * Return an array representing a Ganalytics object for the Twilio SendGrid API
     *
     * @return null|array
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return array_filter(['enable' => $this->get_enable(), 'utm_source' => $this->get_campaign_source(), 'utm_medium' => $this->get_campaign_medium(), 'utm_term' => $this->get_campaign_term(), 'utm_content' => $this->get_campaign_content(), 'utm_campaign' => $this->get_campaign_name()], fn(bool|string $value) => $value !== null) ?: null;
    }
}