<?php

declare (strict_types=1);
/**
 * This library allows you to quickly and easily send emails through Twilio
 * SendGrid using PHP.
 *
 * @package SendGrid\Mail
 */
class Twilio_Email extends Base_Send_Grid_Client_Interface
{
    /**
     * Set up the HTTP Client.
     *
     * @param string $username Username to authenticate with
     * @param string $password Password to authenticate with
     * @param array $options An array of options, currently only "host", "curl",
     *                       "version", "verify_ssl", and "impersonateSubuser",
     *                       are implemented.
     */
    public function __construct($username, $password, $options = [])
    {
        $auth = 'Authorization: Basic ' . \base64_encode("{$username}:{$password}");
        $host = 'https://email.twilio.com';
        parent::__construct($auth, $host, $options);
    }
}