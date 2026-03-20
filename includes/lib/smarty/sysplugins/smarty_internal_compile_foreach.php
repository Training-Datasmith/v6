<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Foreach
 * Compiles the {foreach} {foreachelse} {/foreach} tags
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Foreach Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Foreach extends Smarty_internal_compile_private_foreach_Section
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = ['from', 'item'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['name', 'key', 'properties'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['from', 'item', 'key', 'name'];
    /**
     * counter
     *
     * @var int
     */
    public $counter = 0;
    /**
     * Name of this tag
     *
     * @var string
     */
    public $tag_name = 'foreach';
    /**
     * Valid properties of $smarty.foreach.name.xxx variable
     *
     * @var array
     */
    public $name_properties = ['first', 'last', 'index', 'iteration', 'show', 'total'];
    /**
     * Valid properties of $item@xxx variable
     *
     * @var array
     */
    public $item_properties = ['first', 'last', 'index', 'iteration', 'show', 'total', 'key'];
    /**
     * Flag if tag had name attribute
     *
     * @var bool
     */
    public $is_named = false;
    /**
     * Compiles code for the {foreach} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     * @throws \SmartyException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        $compiler->loop_nesting++;
        // init
        $this->is_named = false;
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        $from = $_attr['from'];
        $item = $compiler->get_id($_attr['item']);
        if ($item === false) {
            $item = $compiler->get_variable_name($_attr['item']);
        }
        $key = $name = null;
        $attributes = ['item' => $item];
        if (isset($_attr['key'])) {
            $key = $compiler->get_id($_attr['key']);
            if ($key === false) {
                $key = $compiler->get_variable_name($_attr['key']);
            }
            $attributes['key'] = $key;
        }
        if (isset($_attr['name'])) {
            $this->is_named = true;
            $name = $attributes['name'] = $compiler->get_id($_attr['name']);
        }
        foreach ($attributes as $a => $v) {
            if ($v === false) {
                $compiler->trigger_template_error("'{$a}' attribute/variable has illegal value", null, true);
            }
        }
        $from_name = $compiler->get_variable_name($_attr['from']);
        if ($from_name) {
            foreach (['item', 'key'] as $a) {
                if (isset($attributes[$a]) && $attributes[$a] === $from_name) {
                    $compiler->trigger_template_error("'{$a}' and 'from' may not have same variable name '{$from_name}'", null, true);
                }
            }
        }
        $item_var = "\$_smarty_tpl->tpl_vars['{$item}']";
        $local = '$__foreach_' . $attributes['item'] . '_' . $this->counter++ . '_';
        // search for used tag attributes
        $item_attr = [];
        $named_attr = [];
        $this->scan_for_properties($attributes, $compiler);
        if (!empty($this->match_results['item'])) {
            $item_attr = $this->match_results['item'];
        }
        if (!empty($this->match_results['named'])) {
            $named_attr = $this->match_results['named'];
        }
        if (isset($_attr['properties']) && preg_match_all('/[\'](.*?)[\']/', $_attr['properties'], $match)) {
            foreach ($match[1] as $prop) {
                if (in_array($prop, $this->item_properties)) {
                    $item_attr[$prop] = true;
                } else {
                    $compiler->trigger_template_error("Invalid property '{$prop}'", null, true);
                }
            }
            if ($this->is_named) {
                foreach ($match[1] as $prop) {
                    if (in_array($prop, $this->name_properties)) {
                        $name_attr[$prop] = true;
                    } else {
                        $compiler->trigger_template_error("Invalid property '{$prop}'", null, true);
                    }
                }
            }
        }
        if (isset($item_attr['first'])) {
            $item_attr['index'] = true;
        }
        if (isset($named_attr['first'])) {
            $named_attr['index'] = true;
        }
        if (isset($named_attr['last'])) {
            $named_attr['iteration'] = true;
            $named_attr['total'] = true;
        }
        if (isset($item_attr['last'])) {
            $item_attr['iteration'] = true;
            $item_attr['total'] = true;
        }
        if (isset($named_attr['show'])) {
            $named_attr['total'] = true;
        }
        if (isset($item_attr['show'])) {
            $item_attr['total'] = true;
        }
        $key_term = '';
        if (isset($attributes['key'])) {
            $key_term = "\$_smarty_tpl->tpl_vars['{$key}']->value => ";
        }
        if (isset($item_attr['key'])) {
            $key_term = "{$item_var}->key => ";
        }
        if ($this->is_named) {
            $foreach_var = "\$_smarty_tpl->tpl_vars['__smarty_foreach_{$attributes['name']}']";
        }
        $need_total = isset($item_attr['total']);
        // Register tag
        $this->open_tag($compiler, 'foreach', ['foreach', $compiler->nocache, $local, $item_var, empty($item_attr) ? 1 : 2]);
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        // generate output code
        $output = "<?php\n";
        $output .= "\$_from = \$_smarty_tpl->smarty->ext->_foreach->init(\$_smarty_tpl, {$from}, " . var_export($item, true);
        if ($name || $need_total || $key) {
            $output .= ', ' . var_export($need_total, true);
        }
        if ($name || $key) {
            $output .= ', ' . var_export($key, true);
        }
        if ($name) {
            $output .= ', ' . var_export($name, true) . ', ' . var_export($named_attr, true);
        }
        $output .= ");\n";
        if (isset($item_attr['show'])) {
            $output .= "{$item_var}->show = ({$item_var}->total > 0);\n";
        }
        if (isset($item_attr['iteration'])) {
            $output .= "{$item_var}->iteration = 0;\n";
        }
        if (isset($item_attr['index'])) {
            $output .= "{$item_var}->index = -1;\n";
        }
        $output .= "{$item_var}->do_else = true;\n";
        $output .= "if (\$_from !== null) foreach (\$_from as {$key_term}{$item_var}->value) {\n";
        $output .= "{$item_var}->do_else = false;\n";
        if (isset($attributes['key']) && isset($item_attr['key'])) {
            $output .= "\$_smarty_tpl->tpl_vars['{$key}']->value = {$item_var}->key;\n";
        }
        if (isset($item_attr['iteration'])) {
            $output .= "{$item_var}->iteration++;\n";
        }
        if (isset($item_attr['index'])) {
            $output .= "{$item_var}->index++;\n";
        }
        if (isset($item_attr['first'])) {
            $output .= "{$item_var}->first = !{$item_var}->index;\n";
        }
        if (isset($item_attr['last'])) {
            $output .= "{$item_var}->last = {$item_var}->iteration === {$item_var}->total;\n";
        }
        if (isset($foreach_var)) {
            if (isset($named_attr['iteration'])) {
                $output .= "{$foreach_var}->value['iteration']++;\n";
            }
            if (isset($named_attr['index'])) {
                $output .= "{$foreach_var}->value['index']++;\n";
            }
            if (isset($named_attr['first'])) {
                $output .= "{$foreach_var}->value['first'] = !{$foreach_var}->value['index'];\n";
            }
            if (isset($named_attr['last'])) {
                $output .= "{$foreach_var}->value['last'] = {$foreach_var}->value['iteration'] === {$foreach_var}->value['total'];\n";
            }
        }
        if (!empty($item_attr)) {
            $output .= "{$local}saved = {$item_var};\n";
        }
        $output .= '?>';
        return $output;
    }
    /**
     * Compiles code for to restore saved template variables
     *
     * @param int $levels number of levels to restore
     *
     * @return string compiled code
     */
    public function compile_restore($levels)
    {
        return "\$_smarty_tpl->smarty->ext->_foreach->restore(\$_smarty_tpl, {$levels});";
    }
}
/**
 * Smarty Internal Plugin Compile Foreachelse Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Foreachelse extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {foreachelse} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        list($open_tag, $nocache, $local, $item_var, $restore) = $this->close_tag($compiler, ['foreach']);
        $this->open_tag($compiler, 'foreachelse', ['foreachelse', $nocache, $local, $item_var, 0]);
        $output = "<?php\n";
        if ($restore === 2) {
            $output .= "{$item_var} = {$local}saved;\n";
        }
        $output .= "}\nif ({$item_var}->do_else) {\n?>";
        return $output;
    }
}
/**
 * Smarty Internal Plugin Compile Foreachclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Foreachclose extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {/foreach} tag
     *
     * @param array                                 $args     array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler compiler object
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     */
    public function compile($args, Smarty_internal_template_Compiler_Base $compiler)
    {
        $compiler->loop_nesting--;
        // must endblock be nocache?
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }
        list($open_tag, $compiler->nocache, $local, $item_var, $restore) = $this->close_tag($compiler, ['foreach', 'foreachelse']);
        $output = "<?php\n";
        if ($restore === 2) {
            $output .= "{$item_var} = {$local}saved;\n";
        }
        $output .= "}\n";
        /* @var Smarty_Internal_Compile_Foreach $foreachCompiler */
        $foreach_compiler = $compiler->get_tag_compiler('foreach');
        $output .= $foreach_compiler->compile_restore(1);
        $output .= '?>';
        return $output;
    }
}