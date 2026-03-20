<?php

declare (strict_types=1);
/**
 * Smarty Method GetDefaultModifiers
 *
 * Smarty::getDefaultModifiers() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Default_Modifiers
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Get default modifiers
     *
     * @api Smarty::getDefaultModifiers()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     *
     * @return array list of default modifiers
     */
    public function get_default_modifiers(Smarty_internal_template_Base $obj)
    {
        $smarty = $obj->_get_smarty_obj();
        return $smarty->default_modifiers;
    }
}