<?php

declare (strict_types=1);
/**
 * Smarty Method GetAutoloadFilters
 *
 * Smarty::getAutoloadFilters() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Autoload_Filters extends Smarty_internal_method_set_Autoload_Filters
{
    /**
     * Get autoload filters
     *
     * @api Smarty::getAutoloadFilters()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $type type of filter to get auto loads
     *                                                                              for. Defaults to all autoload
     *                                                                              filters
     *
     * @return array array( 'type1' => array( 'filter1', 'filter2', … ) ) or array( 'filter1', 'filter2', …) if $type
     *                was specified
     * @throws \SmartyException
     */
    public function get_autoload_filters(Smarty_internal_template_Base $obj, $type = null)
    {
        $smarty = $obj->_get_smarty_obj();
        if ($type !== null) {
            $this->_check_filter_type($type);
            return isset($smarty->autoload_filters[$type]) ? $smarty->autoload_filters[$type] : [];
        }
        return $smarty->autoload_filters;
    }
}