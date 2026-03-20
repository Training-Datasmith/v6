<?php

declare (strict_types=1);
/**
 * Smarty Method AddDefaultModifiers
 *
 * Smarty::addDefaultModifiers() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_add_Default_Modifiers
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Add default modifiers
     *
     * @api Smarty::addDefaultModifiers()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param array|string                                                    $modifiers modifier or list of modifiers
     *                                                                                   to add
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function add_default_modifiers(Smarty_internal_template_Base $obj, $modifiers)
    {
        $smarty = $obj->_get_smarty_obj();
        if (is_array($modifiers)) {
            $smarty->default_modifiers = array_merge($smarty->default_modifiers, $modifiers);
        } else {
            $smarty->default_modifiers[] = $modifiers;
        }
        return $obj;
    }
}