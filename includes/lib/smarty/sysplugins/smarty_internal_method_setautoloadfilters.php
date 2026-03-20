<?php

declare (strict_types=1);
/**
 * Smarty Method SetAutoloadFilters
 *
 * Smarty::setAutoloadFilters() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_set_Autoload_Filters
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
     * Set autoload filters
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
    public function set_autoload_filters(Smarty_internal_template_Base $obj, $filters, $type = null)
    {
        $smarty = $obj->_get_smarty_obj();
        if ($type !== null) {
            $this->_check_filter_type($type);
            $smarty->autoload_filters[$type] = (array) $filters;
        } else {
            foreach ((array) $filters as $type => $value) {
                $this->_check_filter_type($type);
            }
            $smarty->autoload_filters = (array) $filters;
        }
        return $obj;
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