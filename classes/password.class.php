<?php

declare (strict_types=1);
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
/**
 * Password functions
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Password
{
    /**
     * Class instance
     */
    private static ?\Password $_instance = null;
    ##############################################
    final private function __construct()
    {
    }
    /**
     * Setup the instance (singleton)
     */
    public static function get_instance(): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    //=====[ Public ]=======================================
    /**
     * Create salt for passwords
     * @author http://www.richardlord.net/blog/php-password-security
     */
    public function create_salt(): string
    {
        return substr(str_pad(dechex(mt_rand()), 8, '0', STR_PAD_LEFT), -8);
    }
    /**
     * Create a salted password
     *
     * @param string $salt
     */
    public function get_salted(string $value, $salt = ''): string
    {
        //If there is no salt get some
        if (empty($salt)) {
            $salt = $this->create_salt();
        }
        //Make it a hash and extra salty
        return hash('whirlpool', $salt . $value . $salt);
    }
    /**
     * Attempts to create a password hash using the older type
     *
     * @param string $value
     * @param string $salt
     */
    public function get_salted_old($value, $salt): string
    {
        return md5(md5($salt) . md5($value));
    }
    /**
     * Update to old password hash
     *
     * @param md5 string $md5
     * @param string $salt
     */
    public function update_old(string $md5, $salt): string
    {
        return md5(md5($salt) . $md5);
    }
}