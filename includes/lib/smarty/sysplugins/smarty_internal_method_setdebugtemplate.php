<?php

declare (strict_types=1);
/**
 * Smarty Method SetDebugTemplate
 *
 * Smarty::setDebugTemplate() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_set_Debug_Template
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * set the debug template
     *
     * @api Smarty::setDebugTemplate()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $tpl_name
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws SmartyException if file is not readable
     */
    public function set_debug_template(Smarty_internal_template_Base $obj, $tpl_name)
    {
        $smarty = $obj->_get_smarty_obj();
        if (!is_readable($tpl_name)) {
            throw new Smarty_Exception("Unknown file '{$tpl_name}'");
        }
        $smarty->debug_tpl = $tpl_name;
        return $obj;
    }
}