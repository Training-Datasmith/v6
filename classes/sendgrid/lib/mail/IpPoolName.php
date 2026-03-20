<?php

declare (strict_types=1);
/**
 * This helper builds the IpPoolName object for a /mail/send API call
 */
namespace Send_Grid\Mail;

use Send_Grid\Helper\Assert;
/**
 * This class is used to construct a IpPoolName object for the /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Ip_Pool_Name implements \JsonSerializable
{
    /**
     * @var $ip_pool_name string The IP Pool that you would like to send
     *                           this email from.
     *                           Minimum length: 2, Maximum Length: 64
     */
    private $ip_pool_name;
    /**
     * Optional constructor
     *
     * @param string|null $ip_pool_name The IP Pool that you would like to
     *                                  send this email from. Minimum length:
     *                                  2, Maximum Length: 64
     * @throws \SendGrid\Mail\TypeException
     */
    public function __construct($ip_pool_name = null)
    {
        if (isset($ip_pool_name)) {
            $this->set_ip_pool_name($ip_pool_name);
        }
    }
    /**
     * Set the ip pool name on a IpPoolName object
     *
     * @param string $ip_pool_name The IP Pool that you would like to
     *                             send this email from. Minimum length:
     *                             2, Maximum Length: 64
     *
     * @throws \SendGrid\Mail\TypeException
     */
    public function set_ip_pool_name($ip_pool_name): void
    {
        Assert::min_length($ip_pool_name, 'ip_pool_name', 2);
        Assert::max_length($ip_pool_name, 'ip_pool_name', 64);
        $this->ip_pool_name = $ip_pool_name;
    }
    /**
     * Retrieve the ip pool name from a IpPoolName object
     *
     * @return string
     */
    public function get_ip_pool_name()
    {
        return $this->ip_pool_name;
    }
    /**
     * Return an array representing a IpPoolName object for the Twilio SendGrid API
     *
     * @return string
     */
    #[\Return_Type_Will_Change]
    public function jsonSerialize()
    {
        return $this->get_ip_pool_name();
    }
}