<?php

declare (strict_types=1);
/**
 * Smarty Method UnregisterObject
 *
 * Smarty::unregisterObject() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_unregister_Object
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Registers plugin to be used in templates
     *
     * @api  Smarty::unregisterObject()
     * @link https://www.smarty.net/docs/en/api.unregister.object.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $object_name name of object
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function unregister_object(Smarty_internal_template_Base $obj, $object_name)
    {
        $smarty = $obj->_get_smarty_obj();
        if (isset($smarty->registered_objects[$object_name])) {
            unset($smarty->registered_objects[$object_name]);
        }
        return $obj;
    }
}