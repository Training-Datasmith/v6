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
 * Encryption controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Encryption
{
    /**
     * Encryption cipher
     *
     * @var string
     */
    private $_cipher;
    /**
     * Initialisation for encryption
     */
    private string $_iv = null;
    /**
     * Encryption key
     *
     * @var string
     */
    private $_key;
    /**
     * Encryption method
     */
    private string|bool $_method = 'openssl';
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;
    ##############################################
    final protected function __construct()
    {
        if (function_exists('openssl_encrypt')) {
            $this->_method = 'openssl';
        } else {
            $this->_method = false;
        }
    }
    /**
     * Setup the instance (singleton)
     */
    public static function get_instance(): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        self::$_instance->setup();
        return self::$_instance;
    }
    //=====[ Public ]=======================================
    /**
     * Decrypt data
     *
     * @param string $data
     * @return string/false
     */
    public function decrypt($data): string|false
    {
        if (!empty($data)) {
            $data_parts = explode(':iv:', $data);
            return openssl_decrypt($data_parts[1], $this->_cipher, $this->_key, 0, $data_parts[0]);
        }
        return false;
    }
    /**
     * Decrypt CC3/CC4 data (deprecated)
     *
     * @param string $data
     * @param string $cart_order_id
     * @return string/false
     */
    public function decrypt_depreciated($data, $cart_order_id): bool
    {
        return false;
    }
    /**
     * Encrypt data
     *
     * @param string $data
     */
    public function encrypt($data): string|false
    {
        if (!empty($data)) {
            return $this->_iv . ':iv:' . openssl_encrypt($data, $this->_cipher, $this->_key, 0, $this->_iv);
        }
        return false;
    }
    /**
     * Get encryption key
     *
     * @return string
     */
    public function get_encrypt_key()
    {
        if ($GLOBALS['config']->has('config', 'enc_key')) {
            $enc_key = $GLOBALS['config']->get('config', 'enc_key');
            if (empty($enc_key)) {
                return $this->set_encrypt_key();
            }
            return $enc_key;
        }
        return $this->set_encrypt_key();
    }
    /**
     * Get encryption method
     *
     * @return string/false
     */
    public function get_encryption_method()
    {
        return $this->_method;
    }
    /**
     * Set encryption key
     *
     * @return string
     */
    public function set_encrypt_key()
    {
        // Older stores used the software license key so lets keep using that if it exists
        $key = $GLOBALS['config']->get('config', 'license_key');
        // If license_key isn't set and we don't have an "enc_key".. make one
        if ((!$key || empty($key)) && !$GLOBALS['config']->has('config', 'enc_key')) {
            $key = random_string();
            $GLOBALS['config']->set('config', 'enc_key', $key);
        } else {
            // Get enc_key
            $key = $GLOBALS['config']->get('config', 'enc_key');
            if (!$key || empty($key)) {
                $key = random_string();
                $GLOBALS['config']->set('config', 'enc_key', $key);
            }
        }
        return $key;
    }
    /**
     * Setup encryption
     *
     * @param string $key
     * @param string $iv
     * @param string $cipher
     * @param string $mode (unused hangover from mcrypt days)
     * @param string $method (unused hangover from mcrypt/openssl switch days)
     */
    public function setup($key = '', $iv = '', $cipher = '', $mode = '', $method = ''): void
    {
        $key = !empty($key) ? $key : $this->get_encrypt_key();
        $this->_method = 'openssl';
        $this->_key = $key;
        $this->_cipher = empty($cipher) ? 'AES-128-CBC' : $cipher;
        $ivlen = openssl_cipher_iv_length($this->_cipher);
        $this->_iv = openssl_random_pseudo_bytes($ivlen);
    }
}