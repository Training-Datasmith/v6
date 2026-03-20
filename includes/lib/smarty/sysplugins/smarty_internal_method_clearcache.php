<?php

declare (strict_types=1);
/**
 * Smarty Method ClearCache
 *
 * Smarty::clearCache() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_clear_Cache
{
    /**
     * Valid for Smarty object
     *
     * @var int
     */
    public $obj_map = 1;
    /**
     * Empty cache for a specific template
     *
     * @api  Smarty::clearCache()
     * @link https://www.smarty.net/docs/en/api.clear.cache.tpl
     *
     * @param \Smarty $smarty
     * @param string  $template_name template name
     * @param string  $cache_id      cache id
     * @param string  $compile_id    compile id
     * @param integer $exp_time      expiration time
     * @param string  $type          resource type
     *
     * @return int number of cache files deleted
     * @throws \SmartyException
     */
    public function clear_cache(Smarty $smarty, $template_name, $cache_id = null, $compile_id = null, $exp_time = null, $type = null)
    {
        $smarty->_clear_template_cache();
        // load cache resource and call clear
        $_cache_resource = Smarty_cache_Resource::load($smarty, $type);
        return $_cache_resource->clear($smarty, $template_name, $cache_id, $compile_id, $exp_time);
    }
}