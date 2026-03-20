<?php

declare (strict_types=1);
/**
 * Smarty Method RegisterCacheResource
 *
 * Smarty::registerCacheResource() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_register_Cache_Resource
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
     * @api  Smarty::registerCacheResource()
     * @link https://www.smarty.net/docs/en/api.register.cacheresource.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $name name of resource type
     * @param \Smarty_CacheResource                                           $resource_handler
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function register_cache_resource(Smarty_internal_template_Base $obj, $name, Smarty_cache_Resource $resource_handler)
    {
        $smarty = $obj->_get_smarty_obj();
        $smarty->registered_cache_resources[$name] = $resource_handler;
        return $obj;
    }
}