<?php

declare (strict_types=1);
/**
 * Smarty {block} tag class
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Block
{
    /**
     * Block name
     *
     * @var string
     */
    public $name = '';
    /**
     * Hide attribute
     *
     * @var bool
     */
    public $hide = false;
    /**
     * Append attribute
     *
     * @var bool
     */
    public $append = false;
    /**
     * prepend attribute
     *
     * @var bool
     */
    public $prepend = false;
    /**
     * Block calls $smarty.block.child
     *
     * @var bool
     */
    public $calls_child = false;
    /**
     * Inheritance child block
     *
     * @var Smarty_Internal_Block|null
     */
    public $child = null;
    /**
     * Inheritance calling parent block
     *
     * @var Smarty_Internal_Block|null
     */
    public $parent = null;
    /**
     * Inheritance Template index
     *
     * @var int
     */
    public $tpl_index = 0;
    /**
     * Smarty_Internal_Block constructor.
     * - if outer level {block} of child template ($state === 1) save it as child root block
     * - otherwise process inheritance and render
     *
     * @param string   $name     block name
     * @param int|null $tplIndex index of outer level {block} if nested
     */
    public function __construct($name, $tpl_index)
    {
        $this->name = $name;
        $this->tpl_index = $tpl_index;
    }
    /**
     * Compiled block code overloaded by {block} class
     *
     * @param \Smarty_Internal_Template $tpl
     */
    public function call_block(Smarty_Internal_Template $tpl)
    {
    }
}