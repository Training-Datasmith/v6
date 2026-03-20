<?php

declare (strict_types=1);
/**
 * Smarty Method AddAutoloadFilters
 *
 * Smarty::addAutoloadFilters() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_add_Autoload_Filters extends Smarty_internal_method_set_Autoload_Filters
{
    /**
     * Add autoload filters
     *
     * @api Smarty::setAutoloadFilters()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param array                                                           $filters filters to load automatically
     * @param string                                                          $type    "pre", "output", … specify
     *                                                                                 the filter type to set.
     *                                                                                 Defaults to none treating
     *                                                                                 $filters' keys as the
     *                                                                                 appropriate types
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws \SmartyException
     */
    public function add_autoload_filters(Smarty_internal_template_Base $obj, $filters, $type = null)
    {
        $smarty = $obj->_get_smarty_obj();
        if ($type !== null) {
            $this->_check_filter_type($type);
            if (!empty($smarty->autoload_filters[$type])) {
                $smarty->autoload_filters[$type] = array_merge($smarty->autoload_filters[$type], (array) $filters);
            } else {
                $smarty->autoload_filters[$type] = (array) $filters;
            }
        } else {
            foreach ((array) $filters as $type => $value) {
                $this->_check_filter_type($type);
                if (!empty($smarty->autoload_filters[$type])) {
                    $smarty->autoload_filters[$type] = array_merge($smarty->autoload_filters[$type], (array) $value);
                } else {
                    $smarty->autoload_filters[$type] = (array) $value;
                }
            }
        }
        return $obj;
    }
}