<?php

declare(strict_types=1);
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
 * Configuration controller
 *
 * @since 5.0.0
 */
class SSL
{
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################
    /**
     * Setup the instance (singleton)
     */
    public static function getInstance(): self
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Validate redirect
     *
     * @param string $redir
     * @return bool
     */
    public function validRedirect($redir): string|bool
    {
        if (preg_match('#^http#iU', $redir)) {
            $standard_domain = preg_replace('#^https?://|^www\.#i', '', (string) $GLOBALS['config']->get('config', 'standard_url'));
            $standard_domain = strtolower(rtrim($standard_domain, '/'));
            $redir_host = strtolower(parse_url($redir, PHP_URL_HOST) ?? '');
            // Strip leading www. for comparison
            $redir_host = preg_replace('#^www\.#i', '', $redir_host);
            return ($redir_host === $standard_domain || str_ends_with($redir_host, '.'.$standard_domain)) ? $redir : false;
        }
        return true;
    }
}
