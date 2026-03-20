<?php

declare (strict_types=1);
/**
 * Smarty Method GetDebugTemplate
 *
 * Smarty::getDebugTemplate() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Debug_Template
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * return name of debugging template
     *
     * @api Smarty::getDebugTemplate()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     *
     * @return string
     */
    public function get_debug_template(Smarty_internal_template_Base $obj)
    {
        $smarty = $obj->_get_smarty_obj();
        return $smarty->debug_tpl;
    }
}