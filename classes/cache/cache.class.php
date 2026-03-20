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
 * Cache controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Cache_Controler
{
    /**
     * Public status
     *
     * @var @string
     */
    public $status_desc = '';
    /**
     * Public status
     *
     * @var @bool
     */
    public $status = false;
    /**
     * Set the cache path
     *
     * @var string
     */
    protected $_cache_path = '';
    /**
     * Make sure the cache doesn't get cleared more than once
     *
     * @var bool
     */
    protected $_cleared = false;
    /**
     * Cache expire
     *
     * @var int
     */
    protected $_expire = 86400;
    /**
     * Cache IDs
     *
     * @var array
     */
    protected $_ids = [];
    /**
     * Cache mode/type
     *
     * @var string
     */
    protected $_mode = 'None';
    /**
     * Cache prefix
     *
     * @var string
     */
    protected $_prefix = '';
    /**
     * File name suffix
     *
     * @var string
     */
    protected $_suffix = '';
    protected $_empties_id = '___EMPTY';
    protected $_empties = [];
    protected $_empties_added = false;
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;
    protected $_dupes = [];
    ##############################################
    protected function __construct()
    {
        $this->_set_prefix();
        if (!$this->set_path()) {
        }
    }
    //=====[ Public ]=======================================
    protected function _set_prefix()
    {
        $this->_prefix = '';
    }
    /**
     * Enable/Disable cache
     *
     * @param bool $enable
     */
    public function enable($enable = true): void
    {
        $this->status = $enable;
        $this->status();
        if ($enable) {
            $this->_get_empties();
        }
    }
    /**
     * Get the current cache type
     *
     * @return string Cache system
     */
    final public function get_cache_system()
    {
        return $this->_mode;
    }
    /**
     * Get cache prefix
     *
     * @return string Cache prefix
     */
    final public function get_cache_prefix()
    {
        return $this->_prefix;
    }
    /**
     * Get cache expiry
     *
     * @return string Cache expiry
     */
    final public function get_cache_expire(): float|int
    {
        return time() + $this->_expire;
    }
    /**
     * Set cache expire time
     *
     * @param int $expire One day
     */
    public function set_expire($expire = 86400): void
    {
        if (is_numeric($expire)) {
            $this->_expire = $expire;
        }
    }
    /**
     * Set cache path to some where else
     *
     * @param string $path
     */
    public function set_path($path = ''): bool
    {
        if (empty($path)) {
            $path = CC_CACHE_DIR;
        } else {
            $ds = substr($path, -1);
            if ($ds != '/' && $ds != '\\') {
                $path .= '/';
            }
        }
        clearstatcache();
        // Clear cached results
        if (is_dir($path) && is_writable($path)) {
            $this->_cache_path = $path;
        } else {
            trigger_error('Could not change cache path (' . $path . ')', E_USER_WARNING);
            return false;
        }
        return true;
    }
    /**
     * Return cache status
     *
     * @return bool
     */
    public function status()
    {
        if (defined('ADMIN_CP') && ADMIN_CP || defined('CC_IN_SETUP') && CC_IN_SETUP) {
            $this->status_desc = 'Always Disabled in ACP or Setup';
            $this->status = false;
        } else {
            $this->status_desc = $this->status ? 'Enabled' : 'Disabled';
        }
        return $this->status;
    }
    /**
     * Exception to status
     *
     * @param string $id
     */
    public function status_exception($id): bool
    {
        return CC_IN_ADMIN === true && preg_match('/^request\./', $id) ? true : false;
    }
    /**
     * Tidy the cache folder
     */
    public function tidy(): bool
    {
        //Loop through the cache folder
        if (($files = glob($this->_cache_path . '*', GLOB_NOSORT)) !== false) {
            foreach ($files as $file) {
                //Delete any file that is not a cache file
                if (!str_ends_with($file, '.cache') && basename($file) !== 'index.php') {
                    @unlink($file);
                }
            }
        }
        //Loop through the cache/skin folder
        if (($files = glob(CC_SKIN_CACHE_DIR . '*', GLOB_NOSORT)) !== false) {
            /**
             * Delete any files
             *
             * We are doing it this way because smarty class may not be loaded
             * so this will be quicker and safer
             */
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        clearstatcache();
        return true;
    }
    //=====[ Private ]=======================================
    /**
     * Clear skin cache
     *
     * @param string $id
     * @return string
     */
    protected function _clear_file_cache(string $prefix = '*', $files = [])
    {
        $cache_files = glob($this->_cache_path . $this->_prefix . $prefix . $this->_suffix, GLOB_NOSORT);
        if (is_array($cache_files)) {
            $files = array_merge($files, $cache_files);
        }
        $css_files = glob($this->_cache_path . $this->_prefix . $prefix . '.css', GLOB_NOSORT);
        if (is_array($css_files)) {
            $files = array_merge($files, $css_files);
        }
        $js_files = glob($this->_cache_path . $this->_prefix . $prefix . '.js', GLOB_NOSORT);
        if (is_array($js_files)) {
            $files = array_merge($files, $js_files);
        }
        $skin_files = glob($this->_cache_path . 'skin/*', GLOB_NOSORT);
        if (is_array($skin_files)) {
            $files = array_merge($files, $skin_files);
        }
        $code_snippets = glob(CC_ROOT_DIR . '/includes/extra/snippet_*.php', GLOB_NOSORT);
        if (is_array($code_snippets)) {
            $files = array_merge($files, $code_snippets);
        }
        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
    /**
     * Make the cache name key
     */
    protected function _make_name(string $id): string
    {
        return $this->_prefix . $id . $this->_suffix;
    }
}