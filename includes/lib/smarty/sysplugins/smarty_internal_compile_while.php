<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile While
 * Compiles the {while} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile While Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_While extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {while} tag
     *
     * @param array                                 $args      array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
     * @param array                                 $parameter array with compilation parameter
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler, $parameter)
    {
        $compiler->loop_nesting++;
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        $this->open_tag($compiler, 'while', $compiler->nocache);
        if (!array_key_exists('if condition', $parameter)) {
            $compiler->trigger_template_error('missing while condition', null, true);
        }
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        if (is_array($parameter['if condition'])) {
            if ($compiler->nocache) {
                // create nocache var to make it know for further compiling
                if (is_array($parameter['if condition']['var'])) {
                    $var = $parameter['if condition']['var']['var'];
                } else {
                    $var = $parameter['if condition']['var'];
                }
                $compiler->set_nocache_in_variable($var);
            }
            $prefix_var = $compiler->get_new_prefix_variable();
            $assign_compiler = new Smarty_Internal_Compile_Assign();
            $assign_attr = [];
            $assign_attr[]['value'] = $prefix_var;
            if (is_array($parameter['if condition']['var'])) {
                $assign_attr[]['var'] = $parameter['if condition']['var']['var'];
                $_output = "<?php while ({$prefix_var} = {$parameter['if condition']['value']}) {?>";
                $_output .= $assign_compiler->compile($assign_attr, $compiler, ['smarty_internal_index' => $parameter['if condition']['var']['smarty_internal_index']]);
            } else {
                $assign_attr[]['var'] = $parameter['if condition']['var'];
                $_output = "<?php while ({$prefix_var} = {$parameter['if condition']['value']}) {?>";
                $_output .= $assign_compiler->compile($assign_attr, $compiler, []);
            }
            return $_output;
        } else {
            return "<?php\n while ({$parameter['if condition']}) {?>";
        }
    }
}
/**
 * Smarty Internal Plugin Compile Whileclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Whileclose extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {/while} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        $compiler->loop_nesting--;
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }
        $compiler->nocache = $this->close_tag($compiler, ['while']);
        return "<?php }?>\n";
    }
}