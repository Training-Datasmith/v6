<?php

declare (strict_types=1);
/**
 * Smarty Method UnregisterFilter
 *
 * Smarty::unregisterFilter() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_unregister_Filter extends Smarty_internal_method_register_Filter
{
    /**
     * Unregisters a filter function
     *
     * @api  Smarty::unregisterFilter()
     *
     * @link https://www.smarty.net/docs/en/api.unregister.filter.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $type filter type
     * @param callback|string                                                 $callback
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws \SmartyException
     */
    public function unregister_filter(Smarty_internal_template_Base $obj, $type, $callback)
    {
        $smarty = $obj->_get_smarty_obj();
        $this->_check_filter_type($type);
        if (isset($smarty->registered_filters[$type])) {
            $name = is_string($callback) ? $callback : $this->_get_filter_name($callback);
            if (isset($smarty->registered_filters[$type][$name])) {
                unset($smarty->registered_filters[$type][$name]);
                if (empty($smarty->registered_filters[$type])) {
                    unset($smarty->registered_filters[$type]);
                }
            }
        }
        return $obj;
    }
}