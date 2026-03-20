<?php

declare (strict_types=1);
/**
 * Inheritance Runtime Methods processBlock, endChild, init
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 **/
class Smarty_Internal_Runtime_Inheritance
{
    /**
     * State machine
     * - 0 idle next extends will create a new inheritance tree
     * - 1 processing child template
     * - 2 wait for next inheritance template
     * - 3 assume parent template, if child will loaded goto state 1
     *     a call to a sub template resets the state to 0
     *
     * @var int
     */
    public $state = 0;
    /**
     * Array of root child {block} objects
     *
     * @var Smarty_Internal_Block[]
     */
    public $child_root = [];
    /**
     * inheritance template nesting level
     *
     * @var int
     */
    public $inheritance_level = 0;
    /**
     * inheritance template index
     *
     * @var int
     */
    public $tpl_index = -1;
    /**
     * Array of template source objects
     *
     * @var Smarty_Template_Source[]
     */
    public $sources = [];
    /**
     * Stack of source objects while executing block code
     *
     * @var Smarty_Template_Source[]
     */
    public $source_stack = [];
    /**
     * Initialize inheritance
     *
     * @param \Smarty_Internal_Template $tpl        template object of caller
     * @param bool                      $initChild  if true init for child template
     * @param array                     $blockNames outer level block name
     */
    public function init(Smarty_Internal_Template $tpl, $init_child, $block_names = [])
    {
        // if called while executing parent template it must be a sub-template with new inheritance root
        if ($init_child && $this->state === 3 && strpos($tpl->template_resource, 'extendsall') === false) {
            $tpl->inheritance = new Smarty_Internal_Runtime_Inheritance();
            $tpl->inheritance->init($tpl, $init_child, $block_names);
            return;
        }
        ++$this->tpl_index;
        $this->sources[$this->tpl_index] = $tpl->source;
        // start of child sub template(s)
        if ($init_child) {
            $this->state = 1;
            if (!$this->inheritance_level) {
                //grab any output of child templates
                ob_start();
            }
            ++$this->inheritance_level;
            //           $tpl->startRenderCallbacks[ 'inheritance' ] = array($this, 'subTemplateStart');
            //           $tpl->endRenderCallbacks[ 'inheritance' ] = array($this, 'subTemplateEnd');
        }
        // if state was waiting for parent change state to parent
        if ($this->state === 2) {
            $this->state = 3;
        }
    }
    /**
     * End of child template(s)
     * - if outer level is reached flush output buffer and switch to wait for parent template state
     *
     * @param \Smarty_Internal_Template $tpl
     * @param null|string               $template optional name of inheritance parent template
     * @param null|string               $uid      uid of inline template
     * @param null|string               $func     function call name of inline template
     *
     * @throws \Exception
     * @throws \SmartyException
     */
    public function end_child(Smarty_Internal_Template $tpl, $template = null, $uid = null, $func = null)
    {
        --$this->inheritance_level;
        if (!$this->inheritance_level) {
            ob_end_clean();
            $this->state = 2;
        }
        if (isset($template) && ($tpl->parent->_is_tpl_obj() && $tpl->parent->source->type !== 'extends' || $tpl->smarty->extends_recursion)) {
            $tpl->_sub_template_render($template, $tpl->cache_id, $tpl->compile_id, $tpl->caching ? 9999 : 0, $tpl->cache_lifetime, [], 2, false, $uid, $func);
        }
    }
    /**
     * Smarty_Internal_Block constructor.
     * - if outer level {block} of child template ($state === 1) save it as child root block
     * - otherwise process inheritance and render
     *
     * @param \Smarty_Internal_Template $tpl
     * @param                           $className
     * @param string                    $name
     * @param int|null                  $tplIndex index of outer level {block} if nested
     *
     * @throws \SmartyException
     */
    public function instance_block(Smarty_Internal_Template $tpl, $class_name, $name, $tpl_index = null)
    {
        $block = new $class_name($name, isset($tpl_index) ? $tpl_index : $this->tpl_index);
        if (isset($this->child_root[$name])) {
            $block->child = $this->child_root[$name];
        }
        if ($this->state === 1) {
            $this->child_root[$name] = $block;
            return;
        }
        // make sure we got child block of child template of current block
        while ($block->child && $block->child->child && $block->tpl_index <= $block->child->tpl_index) {
            $block->child = $block->child->child;
        }
        $this->process($tpl, $block);
    }
    /**
     * Goto child block or render this
     *
     * @param \Smarty_Internal_Template   $tpl
     * @param \Smarty_Internal_Block      $block
     * @param \Smarty_Internal_Block|null $parent
     *
     * @throws \SmartyException
     */
    public function process(Smarty_Internal_Template $tpl, Smarty_Internal_Block $block, Smarty_Internal_Block $parent = null)
    {
        if ($block->hide && !isset($block->child)) {
            return;
        }
        if (isset($block->child) && $block->child->hide && !isset($block->child->child)) {
            $block->child = null;
        }
        $block->parent = $parent;
        if ($block->append && !$block->prepend && isset($parent)) {
            $this->call_parent($tpl, $block, '\'{block append}\'');
        }
        if ($block->calls_child || !isset($block->child) || $block->child->hide && !isset($block->child->child)) {
            $this->call_block($block, $tpl);
        } else {
            $this->process($tpl, $block->child, $block);
        }
        if ($block->prepend && isset($parent)) {
            $this->call_parent($tpl, $block, '{block prepend}');
            if ($block->append) {
                if ($block->calls_child || !isset($block->child) || $block->child->hide && !isset($block->child->child)) {
                    $this->call_block($block, $tpl);
                } else {
                    $this->process($tpl, $block->child, $block);
                }
            }
        }
        $block->parent = null;
    }
    /**
     * Render child on \$smarty.block.child
     *
     * @param \Smarty_Internal_Template $tpl
     * @param \Smarty_Internal_Block    $block
     *
     * @return null|string block content
     * @throws \SmartyException
     */
    public function call_child(Smarty_Internal_Template $tpl, Smarty_Internal_Block $block)
    {
        if (isset($block->child)) {
            $this->process($tpl, $block->child, $block);
        }
    }
    /**
     * Render parent block on \$smarty.block.parent or {block append/prepend}
     *
     * @param \Smarty_Internal_Template $tpl
     * @param \Smarty_Internal_Block    $block
     * @param string                    $tag
     *
     * @return null|string  block content
     * @throws \SmartyException
     */
    public function call_parent(Smarty_Internal_Template $tpl, Smarty_Internal_Block $block, $tag)
    {
        if (isset($block->parent)) {
            $this->call_block($block->parent, $tpl);
        } else {
            throw new Smarty_Exception("inheritance: illegal '{$tag}' used in child template '{$tpl->inheritance->sources[$block->tpl_index]->filepath}' block '{$block->name}'");
        }
    }
    /**
     * render block
     *
     * @param \Smarty_Internal_Block    $block
     * @param \Smarty_Internal_Template $tpl
     */
    public function call_block(Smarty_Internal_Block $block, Smarty_Internal_Template $tpl)
    {
        $this->source_stack[] = $tpl->source;
        $tpl->source = $this->sources[$block->tpl_index];
        $block->call_block($tpl);
        $tpl->source = array_pop($this->source_stack);
    }
}