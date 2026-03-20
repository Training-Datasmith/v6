<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Break
 * Compiles the {break} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Break Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Break extends Smarty_internal_compile_Base
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['levels'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['levels'];
    /**
     * Tag name may be overloaded by Smarty_Internal_Compile_Continue
     *
     * @var string
     */
    public $tag = 'break';
    /**
     * Compiles code for the {break} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        list($levels, $foreach_levels) = $this->check_levels($args, $compiler);
        $output = '<?php ';
        if ($foreach_levels > 0 && $this->tag === 'continue') {
            $foreach_levels--;
        }
        if ($foreach_levels > 0) {
            /* @var Smarty_Internal_Compile_Foreach $foreachCompiler */
            $foreach_compiler = $compiler->get_tag_compiler('foreach');
            $output .= $foreach_compiler->compile_restore($foreach_levels);
        }
        $output .= "{$this->tag} {$levels};?>";
        return $output;
    }
    /**
     * check attributes and return array of break and foreach levels
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return array
     * @throws \SmartyCompilerException
     */
    public function check_levels($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        static $_is_loopy = ['for' => true, 'foreach' => true, 'while' => true, 'section' => true];
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', null, true);
        }
        if (isset($_attr['levels'])) {
            if (!is_numeric($_attr['levels'])) {
                $compiler->trigger_template_error('level attribute must be a numeric constant', null, true);
            }
            $levels = $_attr['levels'];
        } else {
            $levels = 1;
        }
        $level_count = $levels;
        $stack_count = count($compiler->_tag_stack) - 1;
        $foreach_levels = 0;
        $last_tag = '';
        while ($level_count > 0 && $stack_count >= 0) {
            if (isset($_is_loopy[$compiler->_tag_stack[$stack_count][0]])) {
                $last_tag = $compiler->_tag_stack[$stack_count][0];
                if ($level_count === 0) {
                    break;
                }
                $level_count--;
                if ($compiler->_tag_stack[$stack_count][0] === 'foreach') {
                    $foreach_levels++;
                }
            }
            $stack_count--;
        }
        if ($level_count !== 0) {
            $compiler->trigger_template_error("cannot {$this->tag} {$levels} level(s)", null, true);
        }
        if ($last_tag === 'foreach' && $this->tag === 'break' && $foreach_levels > 0) {
            $foreach_levels--;
        }
        return [$levels, $foreach_levels];
    }
}