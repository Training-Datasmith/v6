<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile Section
 * Compiles the {section} {sectionelse} {/section} tags
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile Section Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Section extends Smarty_internal_compile_private_foreach_Section
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = ['name', 'loop'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['name', 'loop'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['start', 'step', 'max', 'show', 'properties'];
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
    public $tag_name = 'section';
    /**
     * Valid properties of $smarty.section.name.xxx variable
     *
     * @var array
     */
    public $name_properties = ['first', 'last', 'index', 'iteration', 'show', 'total', 'rownum', 'index_prev', 'index_next', 'loop'];
    /**
     * {section} tag has no item properties
     *
     * @var array
     */
    public $item_properties = null;
    /**
     * {section} tag has always name attribute
     *
     * @var bool
     */
    public $is_named = true;
    /**
     * Compiles code for the {section} tag
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
        // check and get attributes
        $_attr = $this->get_attributes($compiler, $args);
        $attributes = ['name' => $compiler->get_id($_attr['name'])];
        unset($_attr['name']);
        foreach ($attributes as $a => $v) {
            if ($v === false) {
                $compiler->trigger_template_error("'{$a}' attribute/variable has illegal value", null, true);
            }
        }
        $local = "\$__section_{$attributes['name']}_" . $this->counter++ . '_';
        $section_var = "\$_smarty_tpl->tpl_vars['__smarty_section_{$attributes['name']}']";
        $this->open_tag($compiler, 'section', ['section', $compiler->nocache, $local, $section_var]);
        // maybe nocache because of nocache variables
        $compiler->nocache = $compiler->nocache | $compiler->tag_nocache;
        $init_local = [];
        $init_named_property = [];
        $init_for = [];
        $inc_for = [];
        $cmp_for = [];
        $prop_value = ['index' => "{$section_var}->value['index']", 'show' => 'true', 'step' => 1, 'iteration' => "{$local}iteration"];
        $prop_type = ['index' => 2, 'iteration' => 2, 'show' => 0, 'step' => 0];
        // search for used tag attributes
        $this->scan_for_properties($attributes, $compiler);
        if (!empty($this->match_results['named'])) {
            $named_attr = $this->match_results['named'];
        }
        if (isset($_attr['properties']) && preg_match_all("/['](.*?)[']/", $_attr['properties'], $match)) {
            foreach ($match[1] as $prop) {
                if (in_array($prop, $this->name_properties)) {
                    $named_attr[$prop] = true;
                } else {
                    $compiler->trigger_template_error("Invalid property '{$prop}'", null, true);
                }
            }
        }
        $named_attr['index'] = true;
        $output = "<?php\n";
        foreach ($_attr as $attr_name => $attr_value) {
            switch ($attr_name) {
                case 'loop':
                    if (is_numeric($attr_value)) {
                        $v = (int) $attr_value;
                        $t = 0;
                    } else {
                        $v = "(is_array(@\$_loop={$attr_value}) ? count(\$_loop) : max(0, (int) \$_loop))";
                        $t = 1;
                    }
                    if ($t === 1) {
                        $init_local['loop'] = $v;
                        $v = "{$local}loop";
                    }
                    break;
                case 'show':
                    if (is_bool($attr_value)) {
                        $v = $attr_value ? 'true' : 'false';
                        $t = 0;
                    } else {
                        $v = "(bool) {$attr_value}";
                        $t = 3;
                    }
                    break;
                case 'step':
                    if (is_numeric($attr_value)) {
                        $v = (int) $attr_value;
                        $v = $v === 0 ? 1 : $v;
                        $t = 0;
                        break;
                    }
                    $init_local['step'] = "((int)@{$attr_value}) === 0 ? 1 : (int)@{$attr_value}";
                    $v = "{$local}step";
                    $t = 2;
                    break;
                case 'max':
                case 'start':
                    if (is_numeric($attr_value)) {
                        $v = (int) $attr_value;
                        $t = 0;
                        break;
                    }
                    $v = "(int)@{$attr_value}";
                    $t = 3;
                    break;
            }
            if ($t === 3 && $compiler->get_id($attr_value)) {
                $t = 1;
            }
            $prop_value[$attr_name] = $v;
            $prop_type[$attr_name] = $t;
        }
        if (isset($named_attr['step'])) {
            $init_named_property['step'] = $prop_value['step'];
        }
        if (isset($named_attr['iteration'])) {
            $prop_value['iteration'] = "{$section_var}->value['iteration']";
        }
        $inc_for['iteration'] = "{$prop_value['iteration']}++";
        $init_for['iteration'] = "{$prop_value['iteration']} = 1";
        if ($prop_type['step'] === 0) {
            if ($prop_value['step'] === 1) {
                $inc_for['index'] = "{$section_var}->value['index']++";
            } elseif ($prop_value['step'] > 1) {
                $inc_for['index'] = "{$section_var}->value['index'] += {$prop_value['step']}";
            } else {
                $inc_for['index'] = "{$section_var}->value['index'] -= " . -$prop_value['step'];
            }
        } else {
            $inc_for['index'] = "{$section_var}->value['index'] += {$prop_value['step']}";
        }
        if (!isset($prop_value['max'])) {
            $prop_value['max'] = $prop_value['loop'];
            $prop_type['max'] = $prop_type['loop'];
        } elseif ($prop_type['max'] !== 0) {
            $prop_value['max'] = "{$prop_value['max']} < 0 ? {$prop_value['loop']} : {$prop_value['max']}";
            $prop_type['max'] = 1;
        } else if ($prop_value['max'] < 0) {
            $prop_value['max'] = $prop_value['loop'];
            $prop_type['max'] = $prop_type['loop'];
        }
        if (!isset($prop_value['start'])) {
            $start_code = [1 => "{$prop_value['step']} > 0 ? ", 2 => '0', 3 => ' : ', 4 => $prop_value['loop'], 5 => ' - 1'];
            if ($prop_type['loop'] === 0) {
                $start_code[5] = '';
                $start_code[4] = $prop_value['loop'] - 1;
            }
            if ($prop_type['step'] === 0) {
                if ($prop_value['step'] > 0) {
                    $start_code = [1 => '0'];
                    $prop_type['start'] = 0;
                } else {
                    $start_code[1] = $start_code[2] = $start_code[3] = '';
                    $prop_type['start'] = $prop_type['loop'];
                }
            } else {
                $prop_type['start'] = 1;
            }
            $prop_value['start'] = join('', $start_code);
        } else {
            $start_code = [1 => "{$prop_value['start']} < 0 ? ", 2 => 'max(', 3 => "{$prop_value['step']} > 0 ? ", 4 => '0', 5 => ' : ', 6 => '-1', 7 => ', ', 8 => "{$prop_value['start']} + {$prop_value['loop']}", 10 => ')', 11 => ' : ', 12 => 'min(', 13 => $prop_value['start'], 14 => ', ', 15 => "{$prop_value['step']} > 0 ? ", 16 => $prop_value['loop'], 17 => ' : ', 18 => $prop_type['loop'] === 0 ? $prop_value['loop'] - 1 : "{$prop_value['loop']} - 1", 19 => ')'];
            if ($prop_type['step'] === 0) {
                $start_code[3] = $start_code[5] = $start_code[15] = $start_code[17] = '';
                if ($prop_value['step'] > 0) {
                    $start_code[6] = $start_code[18] = '';
                } else {
                    $start_code[4] = $start_code[16] = '';
                }
            }
            if ($prop_type['start'] === 0) {
                if ($prop_type['loop'] === 0) {
                    $start_code[8] = $prop_value['start'] + $prop_value['loop'];
                }
                $prop_type['start'] = $prop_type['step'] + $prop_type['loop'];
                $start_code[1] = '';
                if ($prop_value['start'] < 0) {
                    for ($i = 11; $i <= 19; $i++) {
                        $start_code[$i] = '';
                    }
                    if ($prop_type['start'] === 0) {
                        $start_code = [max($prop_value['step'] > 0 ? 0 : -1, $prop_value['start'] + $prop_value['loop'])];
                    }
                } else {
                    for ($i = 1; $i <= 11; $i++) {
                        $start_code[$i] = '';
                    }
                    if ($prop_type['start'] === 0) {
                        $start_code = [min($prop_value['step'] > 0 ? $prop_value['loop'] : $prop_value['loop'] - 1, $prop_value['start'])];
                    }
                }
            }
            $prop_value['start'] = join('', $start_code);
        }
        if ($prop_type['start'] !== 0) {
            $init_local['start'] = $prop_value['start'];
            $prop_value['start'] = "{$local}start";
        }
        $init_for['index'] = "{$section_var}->value['index'] = {$prop_value['start']}";
        if (!isset($_attr['start']) && !isset($_attr['step']) && !isset($_attr['max'])) {
            $prop_value['total'] = $prop_value['loop'];
            $prop_type['total'] = $prop_type['loop'];
        } else {
            $prop_type['total'] = $prop_type['start'] + $prop_type['loop'] + $prop_type['step'] + $prop_type['max'];
            if ($prop_type['total'] === 0) {
                $prop_value['total'] = min(ceil(($prop_value['step'] > 0 ? $prop_value['loop'] - $prop_value['start'] : (int) $prop_value['start'] + 1) / abs($prop_value['step'])), $prop_value['max']);
            } else {
                $total_code = [1 => 'min(', 2 => 'ceil(', 3 => '(', 4 => "{$prop_value['step']} > 0 ? ", 5 => $prop_value['loop'], 6 => ' - ', 7 => $prop_value['start'], 8 => ' : ', 9 => $prop_value['start'], 10 => '+ 1', 11 => ')', 12 => '/ ', 13 => 'abs(', 14 => $prop_value['step'], 15 => ')', 16 => ')', 17 => ", {$prop_value['max']})"];
                if (!isset($prop_value['max'])) {
                    $total_code[1] = $total_code[17] = '';
                }
                if ($prop_type['loop'] + $prop_type['start'] === 0) {
                    $total_code[5] = $prop_value['loop'] - $prop_value['start'];
                    $total_code[6] = $total_code[7] = '';
                }
                if ($prop_type['start'] === 0) {
                    $total_code[9] = (int) $prop_value['start'] + 1;
                    $total_code[10] = '';
                }
                if ($prop_type['step'] === 0) {
                    $total_code[13] = $total_code[15] = '';
                    if ($prop_value['step'] === 1 || $prop_value['step'] === -1) {
                        $total_code[2] = $total_code[12] = $total_code[14] = $total_code[16] = '';
                    } elseif ($prop_value['step'] < 0) {
                        $total_code[14] = -$prop_value['step'];
                    }
                    $total_code[4] = '';
                    if ($prop_value['step'] > 0) {
                        $total_code[8] = $total_code[9] = $total_code[10] = '';
                    } else {
                        $total_code[5] = $total_code[6] = $total_code[7] = $total_code[8] = '';
                    }
                }
                $prop_value['total'] = join('', $total_code);
            }
        }
        if (isset($named_attr['loop'])) {
            $init_named_property['loop'] = "'loop' => {$prop_value['loop']}";
        }
        if (isset($named_attr['total'])) {
            $init_named_property['total'] = "'total' => {$prop_value['total']}";
            if ($prop_type['total'] > 0) {
                $prop_value['total'] = "{$section_var}->value['total']";
            }
        } elseif ($prop_type['total'] > 0) {
            $init_local['total'] = $prop_value['total'];
            $prop_value['total'] = "{$local}total";
        }
        $cmp_for['iteration'] = "{$prop_value['iteration']} <= {$prop_value['total']}";
        foreach ($init_local as $key => $code) {
            $output .= "{$local}{$key} = {$code};\n";
        }
        $_vars = 'array(' . join(', ', $init_named_property) . ')';
        $output .= "{$section_var} = new Smarty_Variable({$_vars});\n";
        $cond_code = "{$prop_value['total']} !== 0";
        if ($prop_type['total'] === 0) {
            if ($prop_value['total'] === 0) {
                $cond_code = 'false';
            } else {
                $cond_code = 'true';
            }
        }
        if ($prop_type['show'] > 0) {
            $output .= "{$local}show = {$prop_value['show']} ? {$cond_code} : false;\n";
            $output .= "if ({$local}show) {\n";
        } elseif ($prop_value['show'] === 'true') {
            $output .= "if ({$cond_code}) {\n";
        } else {
            $output .= "if (false) {\n";
        }
        $jinit = join(', ', $init_for);
        $jcmp = join(', ', $cmp_for);
        $jinc = join(', ', $inc_for);
        $output .= "for ({$jinit}; {$jcmp}; {$jinc}){\n";
        if (isset($named_attr['rownum'])) {
            $output .= "{$section_var}->value['rownum'] = {$prop_value['iteration']};\n";
        }
        if (isset($named_attr['index_prev'])) {
            $output .= "{$section_var}->value['index_prev'] = {$prop_value['index']} - {$prop_value['step']};\n";
        }
        if (isset($named_attr['index_next'])) {
            $output .= "{$section_var}->value['index_next'] = {$prop_value['index']} + {$prop_value['step']};\n";
        }
        if (isset($named_attr['first'])) {
            $output .= "{$section_var}->value['first'] = ({$prop_value['iteration']} === 1);\n";
        }
        if (isset($named_attr['last'])) {
            $output .= "{$section_var}->value['last'] = ({$prop_value['iteration']} === {$prop_value['total']});\n";
        }
        $output .= '?>';
        return $output;
    }
}
/**
 * Smarty Internal Plugin Compile Sectionelse Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Sectionelse extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {sectionelse} tag
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
        list($open_tag, $nocache, $local, $section_var) = $this->close_tag($compiler, ['section']);
        $this->open_tag($compiler, 'sectionelse', ['sectionelse', $nocache, $local, $section_var]);
        return "<?php }} else {\n ?>";
    }
}
/**
 * Smarty Internal Plugin Compile Sectionclose Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_Internal_Compile_Sectionclose extends Smarty_internal_compile_Base
{
    /**
     * Compiles code for the {/section} tag
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
        list($open_tag, $compiler->nocache, $local, $section_var) = $this->close_tag($compiler, ['section', 'sectionelse']);
        $output = "<?php\n";
        if ($open_tag === 'sectionelse') {
            $output .= "}\n";
        } else {
            $output .= "}\n}\n";
        }
        $output .= '?>';
        return $output;
    }
}