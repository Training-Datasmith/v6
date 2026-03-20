<?php

declare (strict_types=1);
/**
 * Smarty Method LoadFilter
 *
 * Smarty::loadFilter() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_load_Filter
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
     * load a filter of specified type and name
     *
     * @api  Smarty::loadFilter()
     *
     * @link https://www.smarty.net/docs/en/api.load.filter.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $type filter type
     * @param string                                                          $name filter name
     *
     * @return bool
     * @throws SmartyException if filter could not be loaded
     */
    public function load_filter(Smarty_internal_template_Base $obj, $type, $name)
    {
        $smarty = $obj->_get_smarty_obj();
        $this->_check_filter_type($type);
        $_plugin = "smarty_{$type}filter_{$name}";
        $_filter_name = $_plugin;
        if (is_callable($_plugin)) {
            $smarty->registered_filters[$type][$_filter_name] = $_plugin;
            return true;
        }
        if ($smarty->load_plugin($_plugin)) {
            if (class_exists($_plugin, false)) {
                $_plugin = [$_plugin, 'execute'];
            }
            if (is_callable($_plugin)) {
                $smarty->registered_filters[$type][$_filter_name] = $_plugin;
                return true;
            }
        }
        throw new Smarty_Exception("{$type}filter '{$name}' not found or callable");
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