<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile If
 * Compiles the {if} {else} {elseif} {/if} tags
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile If Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_If extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {if} tag
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
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        $this->open_tag($compiler, 'if', [1, $compiler->nocache]);
        // must whole block be nocache ?
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        if (!isset($parameter['if condition'])) {
            $compiler->trigger_template_error('missing if condition', null, true);
        }
        if (is_array($parameter['if condition'])) {
            if (is_array($parameter['if condition']['var'])) {
                $var = $parameter['if condition']['var']['var'];
            } else {
                $var = $parameter['if condition']['var'];
            }
            if ($compiler->nocache) {
                // create nocache var to make it know for further compiling
                $compiler->set_nocache_in_variable($var);
            }
            $prefix_var = $compiler->get_new_prefix_variable();
            $_output = "<?php {$prefix_var} = {$parameter['if condition']['value']};?>\n";
            $assign_attr = [];
            $assign_attr[]['value'] = $prefix_var;
            $assign_compiler = new Smarty_Internal_Compile_Assign();
            if (is_array($parameter['if condition']['var'])) {
                $assign_attr[]['var'] = $parameter['if condition']['var']['var'];
                $_output .= $assign_compiler->compile($assign_attr, $compiler, ['smarty_internal_index' => $parameter['if condition']['var']['smarty_internal_index']]);
            } else {
                $assign_attr[]['var'] = $parameter['if condition']['var'];
                $_output .= $assign_compiler->compile($assign_attr, $compiler, []);
            }
            $_output .= "<?php if ({$prefix_var}) {?>";
            return $_output;
        } else {
            return "<?php if ({$parameter['if condition']}) {?>";
        }
    }
}
/**
 * Smarty Internal Plugin Compile Else Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Else extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {else} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        list($nesting, $compiler->tag_nocache) = $this->close_tag($compiler, ['if', 'elseif']);
        $this->open_tag($compiler, 'else', [$nesting, $compiler->tag_nocache]);
        return '<?php } else { ?>';
    }
}
/**
 * Smarty Internal Plugin Compile ElseIf Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Elseif extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {elseif} tag
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
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        list($nesting, $compiler->tag_nocache) = $this->close_tag($compiler, ['if', 'elseif']);
        if (!isset($parameter['if condition'])) {
            $compiler->trigger_template_error('missing elseif condition', null, true);
        }
        $assign_code = '';
        $var = '';
        if (is_array($parameter['if condition'])) {
            $condition_by_assign = true;
            if (is_array($parameter['if condition']['var'])) {
                $var = $parameter['if condition']['var']['var'];
            } else {
                $var = $parameter['if condition']['var'];
            }
            if ($compiler->nocache) {
                // create nocache var to make it know for further compiling
                $compiler->set_nocache_in_variable($var);
            }
            $prefix_var = $compiler->get_new_prefix_variable();
            $assign_code = "<?php {$prefix_var} = {$parameter['if condition']['value']};?>\n";
            $assign_compiler = new Smarty_Internal_Compile_Assign();
            $assign_attr = [];
            $assign_attr[]['value'] = $prefix_var;
            if (is_array($parameter['if condition']['var'])) {
                $assign_attr[]['var'] = $parameter['if condition']['var']['var'];
                $assign_code .= $assign_compiler->compile($assign_attr, $compiler, ['smarty_internal_index' => $parameter['if condition']['var']['smarty_internal_index']]);
            } else {
                $assign_attr[]['var'] = $parameter['if condition']['var'];
                $assign_code .= $assign_compiler->compile($assign_attr, $compiler, []);
            }
        } else {
            $condition_by_assign = false;
        }
        $prefix_code = $compiler->get_prefix_code();
        if (empty($prefix_code)) {
            if ($condition_by_assign) {
                $this->open_tag($compiler, 'elseif', [$nesting + 1, $compiler->tag_nocache]);
                $_output = $compiler->append_code("<?php } else {\n?>", $assign_code);
                return $compiler->append_code($_output, "<?php if ({$prefix_var}) {?>");
            } else {
                $this->open_tag($compiler, 'elseif', [$nesting, $compiler->tag_nocache]);
                return "<?php } elseif ({$parameter['if condition']}) {?>";
            }
        } else {
            $_output = $compiler->append_code("<?php } else {\n?>", $prefix_code);
            $this->open_tag($compiler, 'elseif', [$nesting + 1, $compiler->tag_nocache]);
            if ($condition_by_assign) {
                $_output = $compiler->append_code($_output, $assign_code);
                return $compiler->append_code($_output, "<?php if ({$prefix_var}) {?>");
            } else {
                return $compiler->append_code($_output, "<?php if ({$parameter['if condition']}) {?>");
            }
        }
    }
}
/**
 * Smarty Internal Plugin Compile Ifclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Ifclose extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {/if} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }
        list($nesting, $compiler->nocache) = $this->close_tag($compiler, ['if', 'else', 'elseif']);
        $tmp = '';
        for ($i = 0; $i < $nesting; $i++) {
            $tmp .= '}';
        }
        return "<?php {$tmp}?>";
    }
}