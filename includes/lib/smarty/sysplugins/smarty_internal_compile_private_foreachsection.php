<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Compile ForeachSection
 * Shared methods for {foreach} {section} tags
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Smarty Internal Plugin Compile ForeachSection Class
 *
 * @package    Smarty
 * @subpackage Compiler
 */
class Smarty_internal_compile_private_foreach_Section extends Smarty_internal_compile_Base
{
    /**
     * Name of this tag
     *
     * @var string
     */
    public $tag_name = '';
    /**
     * Valid properties of $smarty.xxx variable
     *
     * @var array
     */
    public $name_properties = [];
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
     * @var array
     */
    public $match_results = [];
    /**
     * Preg search pattern
     *
     * @var string
     */
    private $property_preg = '';
    /**
     * Offsets in preg match result
     *
     * @var array
     */
    private $result_offsets = [];
    /**
     * Start offset
     *
     * @var int
     */
    private $start_offset = 0;
    /**
     * Scan sources for used tag attributes
     *
     * @param array                                 $attributes
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     *
     * @throws \SmartyException
     */
    public function scan_for_properties($attributes, Smarty_internal_template_Compiler_Base $compiler)
    {
        $this->property_preg = '~(';
        $this->start_offset = 1;
        $this->result_offsets = [];
        $this->match_results = ['named' => [], 'item' => []];
        if (isset($attributes['name'])) {
            $this->build_property_preg(true, $attributes);
        }
        if (isset($this->item_properties)) {
            if ($this->is_named) {
                $this->property_preg .= '|';
            }
            $this->build_property_preg(false, $attributes);
        }
        $this->property_preg .= ')\W~i';
        // Template source
        $this->match_template_source($compiler);
        // Parent template source
        $this->match_parent_template_source($compiler);
        // {block} source
        $this->match_block_source($compiler);
    }
    /**
     * Build property preg string
     *
     * @param bool  $named
     * @param array $attributes
     */
    public function build_property_preg($named, $attributes)
    {
        if ($named) {
            $this->result_offsets['named'] = $this->start_offset = $this->start_offset + 3;
            $this->property_preg .= "(([\$]smarty[.]{$this->tag_name}[.]" . ($this->tag_name === 'section' ? "|[\\[]\\s*" : '') . "){$attributes['name']}[.](";
            $properties = $this->name_properties;
        } else {
            $this->result_offsets['item'] = $this->start_offset = $this->start_offset + 2;
            $this->property_preg .= "([\$]{$attributes['item']}[@](";
            $properties = $this->item_properties;
        }
        $prop_name = reset($properties);
        while ($prop_name) {
            $this->property_preg .= "{$prop_name}";
            $prop_name = next($properties);
            if ($prop_name) {
                $this->property_preg .= '|';
            }
        }
        $this->property_preg .= '))';
    }
    /**
     * Find matches in source string
     *
     * @param string $source
     */
    public function match_property($source)
    {
        preg_match_all($this->property_preg, $source, $match);
        foreach ($this->result_offsets as $key => $offset) {
            foreach ($match[$offset] as $m) {
                if (!empty($m)) {
                    $this->match_results[$key][smarty_strtolower_ascii($m)] = true;
                }
            }
        }
    }
    /**
     * Find matches in template source
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     */
    public function match_template_source(Smarty_internal_template_Compiler_Base $compiler)
    {
        $this->match_property($compiler->parser->lex->data);
    }
    /**
     * Find matches in all parent template source
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     *
     * @throws \SmartyException
     */
    public function match_parent_template_source(Smarty_internal_template_Compiler_Base $compiler)
    {
        // search parent compiler template source
        $next_compiler = $compiler;
        while ($next_compiler !== $next_compiler->parent_compiler) {
            $next_compiler = $next_compiler->parent_compiler;
            if ($compiler !== $next_compiler) {
                // get template source
                $_content = $next_compiler->template->source->get_content();
                if ($_content !== '') {
                    // run pre filter if required
                    if (isset($next_compiler->smarty->autoload_filters['pre']) || isset($next_compiler->smarty->registered_filters['pre'])) {
                        $_content = $next_compiler->smarty->ext->_filter_handler->run_filter('pre', $_content, $next_compiler->template);
                    }
                    $this->match_property($_content);
                }
            }
        }
    }
    /**
     * Find matches in {block} tag source
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     */
    public function match_block_source(Smarty_internal_template_Compiler_Base $compiler)
    {
    }
    /**
     * Compiles code for the {$smarty.foreach.xxx} or {$smarty.section.xxx}tag
     *
     * @param array                                 $args      array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
     * @param array                                 $parameter array with compilation parameter
     *
     * @return string compiled code
     * @throws \SmartyCompilerException
     */
    public function compile_special_variable($args, Smarty_internal_template_Compiler_Base $compiler, $parameter)
    {
        $tag = smarty_strtolower_ascii(trim($parameter[0], '"\''));
        $name = isset($parameter[1]) ? $compiler->get_id($parameter[1]) : false;
        if (!$name) {
            $compiler->trigger_template_error("missing or illegal \$smarty.{$tag} name attribute", null, true);
        }
        $property = isset($parameter[2]) ? smarty_strtolower_ascii($compiler->get_id($parameter[2])) : false;
        if (!$property || !in_array($property, $this->name_properties)) {
            $compiler->trigger_template_error("missing or illegal \$smarty.{$tag} property attribute", null, true);
        }
        $tag_var = "'__smarty_{$tag}_{$name}'";
        return "(isset(\$_smarty_tpl->tpl_vars[{$tag_var}]->value['{$property}']) ? \$_smarty_tpl->tpl_vars[{$tag_var}]->value['{$property}'] : null)";
    }
}