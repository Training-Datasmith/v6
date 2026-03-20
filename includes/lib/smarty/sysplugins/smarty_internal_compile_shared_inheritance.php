<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Shared Inheritance
 * Shared methods for {extends} and {block} tags
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Shared Inheritance Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Shared_Inheritance extends Smarty_internal_compile_Base
{
    /**
     * Compile inheritance initialization code as prefix
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     * @param bool|false                            $initChildSequence if true force child template
     */
    public static function post_compile(Smarty_internal_template_Compiler_Base $compiler, $init_child_sequence = false)
    {
        $compiler->prefix_compiled_code .= "<?php \$_smarty_tpl->_loadInheritance();\n\$_smarty_tpl->inheritance->init(\$_smarty_tpl, " . var_export($init_child_sequence, true) . ");\n?>\n";
    }
    /**
     * Register post compile callback to compile inheritance initialization code
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     * @param bool|false                            $initChildSequence if true force child template
     */
    public function register_init(Smarty_internal_template_Compiler_Base $compiler, $init_child_sequence = false)
    {
        if ($init_child_sequence || !isset($compiler->_cache['inheritanceInit'])) {
            $compiler->register_post_compile_callback(['Smarty_Internal_Compile_Shared_Inheritance', 'postCompile'], [$init_child_sequence], 'inheritanceInit', $init_child_sequence);
            $compiler->_cache['inheritanceInit'] = true;
        }
    }
}