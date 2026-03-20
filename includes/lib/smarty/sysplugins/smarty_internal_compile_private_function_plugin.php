<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Function Plugin
 * Compiles code for the execution of function plugin
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Function Plugin Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Private_Function_Plugin extends Smarty_internal_compile_Base
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = [];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['_any'];
    /**
     * Compiles code for the execution of function plugin
     *
     * @param array                                 $args      array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
     * @param array                                 $parameter array with compilation parameter
     * @param string                                $tag       name of function plugin
     * @param string                                $function  PHP function name
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     * @throws \SmartyException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler, $parameter, $tag, $function)
    {
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        unset($_attr['nocache']);
        // convert attributes into parameter array string
        $_params_array = [];
        foreach ($_attr as $_key => $_value) {
            if (is_int($_key)) {
                $_params_array[] = "{$_key}=>{$_value}";
            } else {
                $_params_array[] = "'{$_key}'=>{$_value}";
            }
        }
        $_params = 'array(' . implode(',', $_params_array) . ')';
        // compile code
        $output = "{$function}({$_params},\$_smarty_tpl)";
        if (!empty($parameter['modifierlist'])) {
            $output = $compiler->compile_tag('private_modifier', [], ['modifierlist' => $parameter['modifierlist'], 'value' => $output]);
        }
        $output = "<?php echo {$output};?>\n";
        return $output;
    }
}