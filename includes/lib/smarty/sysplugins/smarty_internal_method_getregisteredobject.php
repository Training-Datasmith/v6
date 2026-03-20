<?php

declare (strict_types=1);
/**
 * Smarty Method GetRegisteredObject
 *
 * Smarty::getRegisteredObject() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Registered_Object
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * return a reference to a registered object
     *
     * @api  Smarty::getRegisteredObject()
     * @link https://www.smarty.net/docs/en/api.get.registered.object.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $object_name object name
     *
     * @return object
     * @throws \SmartyException if no such object is found
     */
    public function get_registered_object(Smarty_internal_template_Base $obj, $object_name)
    {
        $smarty = $obj->_get_smarty_obj();
        if (!isset($smarty->registered_objects[$object_name])) {
            throw new Smarty_Exception("'{$object_name}' is not a registered object");
        }
        if (!is_object($smarty->registered_objects[$object_name][0])) {
            throw new Smarty_Exception("registered '{$object_name}' is not an object");
        }
        return $smarty->registered_objects[$object_name][0];
    }
}