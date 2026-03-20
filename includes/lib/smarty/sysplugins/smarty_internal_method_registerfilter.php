<?php

declare (strict_types=1);
/**
 * Smarty Method RegisterFilter
 *
 * Smarty::registerFilter() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_register_Filter
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Valid filter types
     *
     * @var array
     */
    private $filter_types = ['pre' => true, 'post' => true, 'output' => true, 'variable' => true];
    /**
     * Registers a filter function
     *
     * @api  Smarty::registerFilter()
     *
     * @link https://www.smarty.net/docs/en/api.register.filter.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $type filter type
     * @param callback                                                        $callback
     * @param string|null                                                     $name optional filter name
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws \SmartyException
     */
    public function register_filter(Smarty_internal_template_Base $obj, $type, $callback, $name = null)
    {
        $smarty = $obj->_get_smarty_obj();
        $this->_check_filter_type($type);
        $name = isset($name) ? $name : $this->_get_filter_name($callback);
        if (!is_callable($callback)) {
            throw new Smarty_Exception("{$type}filter '{$name}' not callable");
        }
        $smarty->registered_filters[$type][$name] = $callback;
        return $obj;
    }
    /**
     * Return internal filter name
     *
     * @param callback $function_name
     *
     * @return string   internal filter name
     */
    public function _get_filter_name($function_name)
    {
        if (is_array($function_name)) {
            $_class_name = is_object($function_name[0]) ? get_class($function_name[0]) : $function_name[0];
            return $_class_name . '_' . $function_name[1];
        } elseif (is_string($function_name)) {
            return $function_name;
        } else {
            return 'closure';
        }
    }
    /**
     * Check if filter type is valid
     *
     * @param string $type
     *
     * @throws \SmartyException
     */
    public function _check_filter_type($type)
    {
        if (!isset($this->filter_types[$type])) {
            throw new Smarty_Exception("Illegal filter type '{$type}'");
        }
    }
}