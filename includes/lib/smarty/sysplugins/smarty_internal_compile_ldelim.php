<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Ldelim
 * Compiles the {ldelim} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Ldelim Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Ldelim extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {ldelim} tag
     * This tag does output the left delimiter
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        $_attr = $this->get_attributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', null, true);
        }
        return $compiler->smarty->left_delimiter;
    }
}