<?php

declare (strict_types=1);
/**
 * Smarty Method UnregisterResource
 *
 * Smarty::unregisterResource() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_unregister_Resource
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
     * @api  Smarty::unregisterResource()
     * @link https://www.smarty.net/docs/en/api.unregister.resource.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $type name of resource type
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function unregister_resource(Smarty_internal_template_Base $obj, $type)
    {
        $smarty = $obj->_get_smarty_obj();
        if (isset($smarty->registered_resources[$type])) {
            unset($smarty->registered_resources[$type]);
        }
        return $obj;
    }
}