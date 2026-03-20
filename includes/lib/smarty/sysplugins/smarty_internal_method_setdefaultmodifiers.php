<?php

declare (strict_types=1);
/**
 * Smarty Method SetDefaultModifiers
 *
 * Smarty::setDefaultModifiers() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_set_Default_Modifiers
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Set default modifiers
     *
     * @api Smarty::setDefaultModifiers()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param array|string                                                    $modifiers modifier or list of modifiers
     *                                                                                   to set
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function set_default_modifiers(Smarty_internal_template_Base $obj, $modifiers)
    {
        $smarty = $obj->_get_smarty_obj();
        $smarty->default_modifiers = (array) $modifiers;
        return $obj;
    }
}