<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin CompileBase
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * This class does extend all internal compile plugins
 *
 * @package    Smarty
 * @subpackage Compiler
 */
abstract class Smarty_internal_compile_Base
{
    /**
     * Array of names of required attribute required by tag
     *
     * @var array
     */
    public $required_attributes = [];
    /**
     * Array of names of optional attribute required by tag
     * use array('_any') if there is no restriction of attributes names
     *
     * @var array
     */
    public $optional_attributes = [];
    /**
     * Shorttag attribute order defined by its names
     *
     * @var array
     */
    public $shorttag_order = [];
    /**
     * Array of names of valid option flags
     *
     * @var array
     */
    public $option_flags = ['nocache'];
    /**
     * Mapping array for boolean option value
     *
     * @var array
     */
    public $option_map = [1 => true, 0 => false, 'true' => true, 'false' => false];
    /**
     * Mapping array with attributes as key
     *
     * @var array
     */
    public $map_cache = [];
    /**
     * This function checks if the attributes passed are valid
     * The attributes passed for the tag to compile are checked against the list of required and
     * optional attributes. Required attributes must be present. Optional attributes are check against
     * the corresponding list. The keyword '_any' specifies that any attribute will be accepted
     * as valid
     *
     * @param object $compiler   compiler object
     * @param array  $attributes attributes applied to the tag
     *
     * @return array  of mapped attributes for further processing
     */
    public function get_attributes($compiler, $attributes)
    {
        $_indexed_attr = [];
        if (!isset($this->map_cache['option'])) {
            $this->map_cache['option'] = array_fill_keys($this->option_flags, true);
        }
        foreach ($attributes as $key => $mixed) {
            // shorthand ?
            if (!is_array($mixed)) {
                // option flag ?
                if (isset($this->map_cache['option'][trim($mixed, '\'"')])) {
                    $_indexed_attr[trim($mixed, '\'"')] = true;
                    // shorthand attribute ?
                } elseif (isset($this->shorttag_order[$key])) {
                    $_indexed_attr[$this->shorttag_order[$key]] = $mixed;
                } else {
                    // too many shorthands
                    $compiler->trigger_template_error('too many shorthand attributes', null, true);
                }
                // named attribute
            } else {
                foreach ($mixed as $k => $v) {
                    // option flag?
                    if (isset($this->map_cache['option'][$k])) {
                        if (is_bool($v)) {
                            $_indexed_attr[$k] = $v;
                        } else {
                            if (is_string($v)) {
                                $v = trim($v, '\'" ');
                            }
                            if (isset($this->option_map[$v])) {
                                $_indexed_attr[$k] = $this->option_map[$v];
                            } else {
                                $compiler->trigger_template_error("illegal value '" . var_export($v, true) . "' for option flag '{$k}'", null, true);
                            }
                        }
                        // must be named attribute
                    } else {
                        $_indexed_attr[$k] = $v;
                    }
                }
            }
        }
        // check if all required attributes present
        foreach ($this->required_attributes as $attr) {
            if (!isset($_indexed_attr[$attr])) {
                $compiler->trigger_template_error("missing '{$attr}' attribute", null, true);
            }
        }
        // check for not allowed attributes
        if ($this->optional_attributes !== ['_any']) {
            if (!isset($this->map_cache['all'])) {
                $this->map_cache['all'] = array_fill_keys(array_merge($this->required_attributes, $this->optional_attributes, $this->option_flags), true);
            }
            foreach ($_indexed_attr as $key => $dummy) {
                if (!isset($this->map_cache['all'][$key]) && $key !== 0) {
                    $compiler->trigger_template_error("unexpected '{$key}' attribute", null, true);
                }
            }
        }
        // default 'false' for all option flags not set
        foreach ($this->option_flags as $flag) {
            if (!isset($_indexed_attr[$flag])) {
                $_indexed_attr[$flag] = false;
            }
        }
        if (isset($_indexed_attr['nocache']) && $_indexed_attr['nocache']) {
            $compiler->tag_nocache = true;
        }
        return $_indexed_attr;
    }
    /**
     * Push opening tag name on stack
     * Optionally additional data can be saved on stack
     *
     * @param object $compiler compiler object
     * @param string $openTag  the opening tag's name
     * @param mixed  $data     optional data saved
     */
    public function open_tag($compiler, $open_tag, $data = null)
    {
        array_push($compiler->_tag_stack, [$open_tag, $data]);
    }
    /**
     * Pop closing tag
     * Raise an error if this stack-top doesn't match with expected opening tags
     *
     * @param object       $compiler    compiler object
     * @param array|string $expectedTag the expected opening tag names
     *
     * @return mixed        any type the opening tag's name or saved data
     */
    public function close_tag($compiler, $expected_tag)
    {
        if (count($compiler->_tag_stack) > 0) {
            // get stacked info
            list($_open_tag, $_data) = array_pop($compiler->_tag_stack);
            // open tag must match with the expected ones
            if (in_array($_open_tag, (array) $expected_tag)) {
                if (is_null($_data)) {
                    // return opening tag
                    return $_open_tag;
                } else {
                    // return restored data
                    return $_data;
                }
            }
            // wrong nesting of tags
            $compiler->trigger_template_error("unclosed '{$compiler->smarty->left_delimiter}{$_open_tag}{$compiler->smarty->right_delimiter}' tag");
            return;
        }
        // wrong nesting of tags
        $compiler->trigger_template_error('unexpected closing tag', null, true);
        return;
    }
}