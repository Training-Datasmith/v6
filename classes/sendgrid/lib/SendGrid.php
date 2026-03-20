<?php

declare (strict_types=1);
/**
 * This library allows you to quickly and easily send emails through Twilio
 * SendGrid using PHP.
 *
 * @package SendGrid\Mail
 */
class Send_Grid extends Base_Send_Grid_Client_Interface
{
    /**
     * Set up the HTTP Client.
     *
     * @param string $apiKey Your Twilio SendGrid API Key.
     * @param array $options An array of options, currently only "host", "curl",
     *                       "version", "verify_ssl", and "impersonateSubuser",
     *                       are implemented.
     */
    public function __construct($api_key, $options = [])
    {
        $auth = 'Authorization: Bearer ' . $api_key;
        $host = 'https://api.sendgrid.com';
        parent::__construct($auth, $host, $options);
    }
}