<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Assign
 * Compiles the {assign} tag
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Assign Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Assign extends Smarty_internal_compile_Base
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $option_flags = ['nocache', 'noscope'];
    /**
     * Valid scope names
     *
     * @var array
     */
    public $valid_scopes = ['local' => Smarty::SCOPE_LOCAL, 'parent' => Smarty::SCOPE_PARENT, 'root' => Smarty::SCOPE_ROOT, 'global' => Smarty::SCOPE_GLOBAL, 'tpl_root' => Smarty::SCOPE_TPL_ROOT, 'smarty' => Smarty::SCOPE_SMARTY];
    /**
     * Compiles code for the {assign} tag
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
        // the following must be assigned at runtime because it will be overwritten in Smarty_Internal_Compile_Append
        $this->required_attributes = ['var', 'value'];
        $this->shorttag_order = ['var', 'value'];
        $this->optional_attributes = ['scope'];
        $this->map_cache = [];
        $_nocache = false;
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        // nocache ?
        if ($_var = $compiler->get_id($_attr['var'])) {
            $_var = "'{$_var}'";
        } else {
            $_var = $_attr['var'];
        }
        if ($compiler->tag_nocache || $compiler->nocache) {
            $_nocache = true;
            // create nocache var to make it know for further compiling
            $compiler->set_nocache_in_variable($_attr['var']);
        }
        // scope setup
        if ($_attr['noscope']) {
            $_scope = -1;
        } else {
            $_scope = $compiler->convert_scope($_attr, $this->valid_scopes);
        }
        // optional parameter
        $_params = '';
        if ($_nocache || $_scope) {
            $_params .= ' ,' . var_export($_nocache, true);
        }
        if ($_scope) {
            $_params .= ' ,' . $_scope;
        }
        if (isset($parameter['smarty_internal_index'])) {
            $output = "<?php \$_tmp_array = isset(\$_smarty_tpl->tpl_vars[{$_var}]) ? \$_smarty_tpl->tpl_vars[{$_var}]->value : array();\n";
            $output .= "if (!(is_array(\$_tmp_array) || \$_tmp_array instanceof ArrayAccess)) {\n";
            $output .= "settype(\$_tmp_array, 'array');\n";
            $output .= "}\n";
            $output .= "\$_tmp_array{$parameter['smarty_internal_index']} = {$_attr['value']};\n";
            $output .= "\$_smarty_tpl->_assignInScope({$_var}, \$_tmp_array{$_params});?>";
        } else {
            $output = "<?php \$_smarty_tpl->_assignInScope({$_var}, {$_attr['value']}{$_params});?>";
        }
        return $output;
    }
}