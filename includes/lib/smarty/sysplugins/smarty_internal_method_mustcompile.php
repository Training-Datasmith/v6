<?php

declare (strict_types=1);
/**
 * Smarty Method MustCompile
 *
 * Smarty_Internal_Template::mustCompile() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_must_Compile
{
    /**
     * Valid for template object
     *
     * @var int
     */
    public $obj_map = 2;
    /**
     * Returns if the current template must be compiled by the Smarty compiler
     * It does compare the timestamps of template source and the compiled templates and checks the force compile
     * configuration
     *
     * @param \Smarty_Internal_Template $_template
     *
     * @return bool
     * @throws \SmartyException
     */
    public function must_compile(Smarty_Internal_Template $_template)
    {
        if (!$_template->source->exists) {
            if ($_template->_is_sub_tpl()) {
                $parent_resource = " in '{$_template->parent->template_resource}'";
            } else {
                $parent_resource = '';
            }
            throw new Smarty_Exception("Unable to load template {$_template->source->type} '{$_template->source->name}'{$parent_resource}");
        }
        if ($_template->must_compile === null) {
            $_template->must_compile = !$_template->source->handler->uncompiled && ($_template->smarty->force_compile || $_template->source->handler->recompiled || !$_template->compiled->exists || $_template->compile_check && $_template->compiled->get_time_stamp() < $_template->source->get_time_stamp());
        }
        return $_template->must_compile;
    }
}