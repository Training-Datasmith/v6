<?php

declare (strict_types=1);
/**
 * Smarty Method RegisterClass
 *
 * Smarty::registerClass() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_register_Class
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Registers static classes to be used in templates
     *
     * @api  Smarty::registerClass()
     * @link https://www.smarty.net/docs/en/api.register.class.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $class_name
     * @param string                                                          $class_impl the referenced PHP class to
     *                                                                                    register
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws \SmartyException
     */
    public function register_class(Smarty_internal_template_Base $obj, $class_name, $class_impl)
    {
        $smarty = $obj->_get_smarty_obj();
        // test if exists
        if (!class_exists($class_impl)) {
            throw new Smarty_Exception("Undefined class '{$class_impl}' in register template class");
        }
        // register the class
        $smarty->registered_classes[$class_name] = $class_impl;
        return $obj;
    }
}