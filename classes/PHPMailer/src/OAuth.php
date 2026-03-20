<?php

declare (strict_types=1);
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5.
 *
 * @see       https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
namespace Php_Mailer\Php_Mailer;

use League\O_Auth2\Client\Grant\Refresh_Token;
use League\O_Auth2\Client\Provider\Abstract_Provider;
use League\O_Auth2\Client\Token\Access_Token;
/**
 * OAuth - OAuth2 authentication wrapper class.
 * Uses the oauth2-client package from the League of Extraordinary Packages.
 *
 * @see     http://oauth2-client.thephpleague.com
 *
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 */
class O_Auth
{
    /**
     * An instance of the League OAuth Client Provider.
     *
     * @var AbstractProvider
     */
    protected $provider;
    /**
     * The current OAuth access token.
     *
     * @var AccessToken
     */
    protected $oauth_token;
    /**
     * The user's email address, usually used as the login ID
     * and also the from address when sending email.
     *
     * @var string
     */
    protected $oauth_user_email = '';
    /**
     * The client secret, generated in the app definition of the service you're connecting to.
     *
     * @var string
     */
    protected $oauth_client_secret = '';
    /**
     * The client ID, generated in the app definition of the service you're connecting to.
     *
     * @var string
     */
    protected $oauth_client_id = '';
    /**
     * The refresh token, used to obtain new AccessTokens.
     *
     * @var string
     */
    protected $oauth_refresh_token = '';
    /**
     * OAuth constructor.
     *
     * @param array $options Associative array containing
     *                       `provider`, `userName`, `clientSecret`, `clientId` and `refreshToken` elements
     */
    public function __construct(array $options)
    {
        $this->provider = $options['provider'];
        $this->oauth_user_email = $options['userName'];
        $this->oauth_client_secret = $options['clientSecret'];
        $this->oauth_client_id = $options['clientId'];
        $this->oauth_refresh_token = $options['refreshToken'];
    }
    /**
     * Get a new RefreshToken.
     *
     * @return RefreshToken
     */
    protected function get_grant()
    {
        return new Refresh_Token();
    }
    /**
     * Get a new AccessToken.
     *
     * @return AccessToken
     */
    protected function get_token()
    {
        return $this->provider->get_access_token($this->get_grant(), ['refresh_token' => $this->oauth_refresh_token]);
    }
    /**
     * Generate a base64-encoded OAuth token.
     */
    public function get_oauth64(): string
    {
        //Get a new token if it's not available or has expired
        if (null === $this->oauth_token || $this->oauth_token->has_expired()) {
            $this->oauth_token = $this->get_token();
        }
        return base64_encode('user=' . $this->oauth_user_email . "\x01auth=Bearer " . $this->oauth_token . "\x01\x01");
    }
}