<?php

declare (strict_types=1);
/**
 * Smarty Method ClearAllCache
 *
 * Smarty::clearAllCache() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_clear_All_Cache
{
    /**
     * Valid for Smarty object
     *
     * @var int
     */
    public $obj_map = 1;
    /**
     * Empty cache folder
     *
     * @api  Smarty::clearAllCache()
     * @link https://www.smarty.net/docs/en/api.clear.all.cache.tpl
     *
     * @param \Smarty $smarty
     * @param integer $exp_time expiration time
     * @param string  $type     resource type
     *
     * @return int number of cache files deleted
     * @throws \SmartyException
     */
    public function clear_all_cache(Smarty $smarty, $exp_time = null, $type = null)
    {
        $smarty->_clear_template_cache();
        // load cache resource and call clearAll
        $_cache_resource = Smarty_cache_Resource::load($smarty, $type);
        return $_cache_resource->clear_all($smarty, $exp_time);
    }
}