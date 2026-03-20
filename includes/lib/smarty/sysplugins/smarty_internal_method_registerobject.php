<?php

declare (strict_types=1);
/**
 * Smarty Method RegisterObject
 *
 * Smarty::registerObject() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_register_Object
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Registers object to be used in templates
     *
     * @api  Smarty::registerObject()
     * @link https://www.smarty.net/docs/en/api.register.object.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $object_name
     * @param object                                                          $object                     the
     *                                                                                                    referenced
     *                                                                                                    PHP
     *                                                                                                    object
     *                                                                                                    to
     *                                                                                                    register
     *
     * @param array                                                           $allowed_methods_properties list of
     *                                                                                                    allowed
     *                                                                                                    methods
     *                                                                                                    (empty
     *                                                                                                    = all)
     *
     * @param bool                                                            $format                     smarty
     *                                                                                                    argument
     *                                                                                                    format,
     *                                                                                                    else
     *                                                                                                    traditional
     *
     * @param array                                                           $block_methods              list of
     *                                                                                                    block-methods
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws \SmartyException
     */
    public function register_object(Smarty_internal_template_Base $obj, $object_name, $object, $allowed_methods_properties = [], $format = true, $block_methods = [])
    {
        $smarty = $obj->_get_smarty_obj();
        // test if allowed methods callable
        if (!empty($allowed_methods_properties)) {
            foreach ((array) $allowed_methods_properties as $method) {
                if (!is_callable([$object, $method]) && !property_exists($object, $method)) {
                    throw new Smarty_Exception("Undefined method or property '{$method}' in registered object");
                }
            }
        }
        // test if block methods callable
        if (!empty($block_methods)) {
            foreach ((array) $block_methods as $method) {
                if (!is_callable([$object, $method])) {
                    throw new Smarty_Exception("Undefined method '{$method}' in registered object");
                }
            }
        }
        // register the object
        $smarty->registered_objects[$object_name] = [$object, (array) $allowed_methods_properties, (bool) $format, (array) $block_methods];
        return $obj;
    }
}