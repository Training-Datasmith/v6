<?php

declare (strict_types=1);
/**
 * Smarty Method UnregisterCacheResource
 *
 * Smarty::unregisterCacheResource() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_unregister_Cache_Resource
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Registers a resource to fetch a template
     *
     * @api  Smarty::unregisterCacheResource()
     * @link https://www.smarty.net/docs/en/api.unregister.cacheresource.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param                                                                 $name
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function unregister_cache_resource(Smarty_internal_template_Base $obj, $name)
    {
        $smarty = $obj->_get_smarty_obj();
        if (isset($smarty->registered_cache_resources[$name])) {
            unset($smarty->registered_cache_resources[$name]);
        }
        return $obj;
    }
}