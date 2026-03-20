<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Function
 * Compiles the {function} {/function} tags
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Function Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Function extends Smarty_internal_compile_Base
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = ['name'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['name'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['_any'];
    /**
     * Compiles code for the {function} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return bool true
     * @throws \SmartyCompilerException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', null, true);
        }
        unset($_attr['nocache']);
        $_name = trim($_attr['name'], '\'"');
        if (!preg_match('/^[a-zA-Z0-9_\x80-\xff]+$/', $_name)) {
            $compiler->trigger_template_error("Function name contains invalid characters: {$_name}", null, true);
        }
        $compiler->parent_compiler->tpl_function[$_name] = [];
        $save = [$_attr, $compiler->parser->current_buffer, $compiler->template->compiled->has_nocache_code, $compiler->template->caching];
        $this->open_tag($compiler, 'function', $save);
        // Init temporary context
        $compiler->parser->current_buffer = new Smarty_internal_parse_Tree_template();
        $compiler->template->compiled->has_nocache_code = false;
        $compiler->save_required_plugins(true);
        return true;
    }
}
/**
 * Smarty Internal Plugin Compile Functionclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Functionclose extends Smarty_internal_compile_Base
{
    /**
     * Compiler object
     *
     * @var object
     */
    private $compiler = null;
    /**
     * Compiles code for the {/function} tag
     *
     * @param array                                        $args     array with attributes from parser
     * @param object|\Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return bool true
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        $this->compiler = $compiler;
        $saved_data = $this->close_tag($compiler, ['function']);
        $_attr = $saved_data[0];
        $_name = trim($_attr['name'], '\'"');
        $compiler->parent_compiler->tpl_function[$_name]['compiled_filepath'] = $compiler->parent_compiler->template->compiled->filepath;
        $compiler->parent_compiler->tpl_function[$_name]['uid'] = $compiler->template->source->uid;
        $_parameter = $_attr;
        unset($_parameter['name']);
        // default parameter
        $_params_array = [];
        foreach ($_parameter as $_key => $_value) {
            if (is_int($_key)) {
                $_params_array[] = "{$_key}=>{$_value}";
            } else {
                $_params_array[] = "'{$_key}'=>{$_value}";
            }
        }
        if (!empty($_params_array)) {
            $_params = 'array(' . implode(',', $_params_array) . ')';
            $_params_code = "\$params = array_merge({$_params}, \$params);\n";
        } else {
            $_params_code = '';
        }
        $_function_code = $compiler->parser->current_buffer;
        // setup buffer for template function code
        $compiler->parser->current_buffer = new Smarty_internal_parse_Tree_template();
        $_func_name = "smarty_template_function_{$_name}_{$compiler->template->compiled->nocache_hash}";
        $_func_name_caching = $_func_name . '_nocache';
        if ($compiler->template->compiled->has_nocache_code) {
            $compiler->parent_compiler->tpl_function[$_name]['call_name_caching'] = $_func_name_caching;
            $output = "<?php\n";
            $output .= $compiler->c_style_comment(" {$_func_name_caching} ") . "\n";
            $output .= "if (!function_exists('{$_func_name_caching}')) {\n";
            $output .= "function {$_func_name_caching} (Smarty_Internal_Template \$_smarty_tpl,\$params) {\n";
            $output .= "ob_start();\n";
            $output .= $compiler->compile_required_plugins();
            $output .= "\$_smarty_tpl->compiled->has_nocache_code = true;\n";
            $output .= $_params_code;
            $output .= "foreach (\$params as \$key => \$value) {\n\$_smarty_tpl->tpl_vars[\$key] = new Smarty_Variable(\$value, \$_smarty_tpl->isRenderingCache);\n}\n";
            $output .= "\$params = var_export(\$params, true);\n";
            $output .= "echo \"/*%%SmartyNocache:{$compiler->template->compiled->nocache_hash}%%*/<?php ";
            $output .= "\\\$_smarty_tpl->smarty->ext->_tplFunction->saveTemplateVariables(\\\$_smarty_tpl, '{$_name}');\nforeach (\$params as \\\$key => \\\$value) {\n\\\$_smarty_tpl->tpl_vars[\\\$key] = new Smarty_Variable(\\\$value, \\\$_smarty_tpl->isRenderingCache);\n}\n?>";
            $output .= "/*/%%SmartyNocache:{$compiler->template->compiled->nocache_hash}%%*/\";?>";
            $compiler->parser->current_buffer->append_subtree($compiler->parser, new Smarty_internal_parse_Tree_tag($compiler->parser, $output));
            $compiler->parser->current_buffer->append_subtree($compiler->parser, $_function_code);
            $output = "<?php echo \"/*%%SmartyNocache:{$compiler->template->compiled->nocache_hash}%%*/<?php ";
            $output .= "\\\$_smarty_tpl->smarty->ext->_tplFunction->restoreTemplateVariables(\\\$_smarty_tpl, '{$_name}');?>\n";
            $output .= "/*/%%SmartyNocache:{$compiler->template->compiled->nocache_hash}%%*/\";\n?>";
            $output .= "<?php echo str_replace('{$compiler->template->compiled->nocache_hash}', \$_smarty_tpl->compiled->nocache_hash ?? '', ob_get_clean());\n";
            $output .= "}\n}\n";
            $output .= $compiler->c_style_comment("/ {$_func_name}_nocache ") . "\n\n";
            $output .= "?>\n";
            $compiler->parser->current_buffer->append_subtree($compiler->parser, new Smarty_internal_parse_Tree_tag($compiler->parser, $output));
            $_function_code = new Smarty_internal_parse_Tree_tag($compiler->parser, preg_replace_callback("/((<\\?php )?echo '\\/\\*%%SmartyNocache:{$compiler->template->compiled->nocache_hash}%%\\*\\/([\\S\\s]*?)\\/\\*\\/%%SmartyNocache:{$compiler->template->compiled->nocache_hash}%%\\*\\/';(\\?>\n)?)/", [$this, 'removeNocache'], $_function_code->to_smarty_php($compiler->parser)));
        }
        $compiler->parent_compiler->tpl_function[$_name]['call_name'] = $_func_name;
        $output = "<?php\n";
        $output .= $compiler->c_style_comment(" {$_func_name} ") . "\n";
        $output .= "if (!function_exists('{$_func_name}')) {\n";
        $output .= "function {$_func_name}(Smarty_Internal_Template \$_smarty_tpl,\$params) {\n";
        $output .= $_params_code;
        $output .= "foreach (\$params as \$key => \$value) {\n\$_smarty_tpl->tpl_vars[\$key] = new Smarty_Variable(\$value, \$_smarty_tpl->isRenderingCache);\n}\n";
        $output .= $compiler->compile_check_plugins(array_merge($compiler->required_plugins['compiled'], $compiler->required_plugins['nocache']));
        $output .= "?>\n";
        $compiler->parser->current_buffer->append_subtree($compiler->parser, new Smarty_internal_parse_Tree_tag($compiler->parser, $output));
        $compiler->parser->current_buffer->append_subtree($compiler->parser, $_function_code);
        $output = "<?php\n}}\n";
        $output .= $compiler->c_style_comment("/ {$_func_name} ") . "\n\n";
        $output .= "?>\n";
        $compiler->parser->current_buffer->append_subtree($compiler->parser, new Smarty_internal_parse_Tree_tag($compiler->parser, $output));
        $compiler->parent_compiler->block_or_function_code .= $compiler->parser->current_buffer->to_smarty_php($compiler->parser);
        // restore old buffer
        $compiler->parser->current_buffer = $saved_data[1];
        // restore old status
        $compiler->restore_required_plugins();
        $compiler->template->compiled->has_nocache_code = $saved_data[2];
        $compiler->template->caching = $saved_data[3];
        return true;
    }
    /**
     * Remove nocache code
     *
     * @param $match
     *
     * @return string
     */
    public function remove_nocache($match)
    {
        $code = preg_replace("/((<\\?php )?echo '\\/\\*%%SmartyNocache:{$this->compiler->template->compiled->nocache_hash}%%\\*\\/)|(\\/\\*\\/%%SmartyNocache:{$this->compiler->template->compiled->nocache_hash}%%\\*\\/';(\\?>\n)?)/", '', $match[0]);
        $code = str_replace(['\\\'', '\\\\\''], ['\'', '\\\''], $code);
        return $code;
    }
}