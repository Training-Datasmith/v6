<?php

declare (strict_types=1);
/**
 * Runtime Extension Capture
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Runtime_Capture
{
    /**
     * Flag that this instance  will not be cached
     *
     * @var bool
     */
    public $is_private_extension = true;
    /**
     * Stack of capture parameter
     *
     * @var array
     */
    private $capture_stack = [];
    /**
     * Current open capture sections
     *
     * @var int
     */
    private $capture_count = 0;
    /**
     * Count stack
     *
     * @var int[]
     */
    private $count_stack = [];
    /**
     * Named buffer
     *
     * @var string[]
     */
    private $named_buffer = [];
    /**
     * Flag if callbacks are registered
     *
     * @var bool
     */
    private $is_registered = false;
    /**
     * Open capture section
     *
     * @param \Smarty_Internal_Template $_template
     * @param string                    $buffer capture name
     * @param string                    $assign variable name
     * @param string                    $append variable name
     */
    public function open(Smarty_Internal_Template $_template, $buffer, $assign, $append)
    {
        if (!$this->is_registered) {
            $this->register($_template);
        }
        $this->capture_stack[] = [$buffer, $assign, $append];
        $this->capture_count++;
        ob_start();
    }
    /**
     * Register callbacks in template class
     *
     * @param \Smarty_Internal_Template $_template
     */
    private function register(Smarty_Internal_Template $_template)
    {
        $_template->start_render_callbacks[] = [$this, 'startRender'];
        $_template->end_render_callbacks[] = [$this, 'endRender'];
        $this->start_render($_template);
        $this->is_registered = true;
    }
    /**
     * Start render callback
     *
     * @param \Smarty_Internal_Template $_template
     */
    public function start_render(Smarty_Internal_Template $_template)
    {
        $this->count_stack[] = $this->capture_count;
        $this->capture_count = 0;
    }
    /**
     * Close capture section
     *
     * @param \Smarty_Internal_Template $_template
     *
     * @throws \SmartyException
     */
    public function close(Smarty_Internal_Template $_template)
    {
        if ($this->capture_count) {
            list($buffer, $assign, $append) = array_pop($this->capture_stack);
            $this->capture_count--;
            if (isset($assign)) {
                $_template->assign($assign, ob_get_contents());
            }
            if (isset($append)) {
                $_template->append($append, ob_get_contents());
            }
            $this->named_buffer[$buffer] = ob_get_clean();
        } else {
            $this->error($_template);
        }
    }
    /**
     * Error exception on not matching {capture}{/capture}
     *
     * @param \Smarty_Internal_Template $_template
     *
     * @throws \SmartyException
     */
    public function error(Smarty_Internal_Template $_template)
    {
        throw new Smarty_Exception("Not matching {capture}{/capture} in '{$_template->template_resource}'");
    }
    /**
     * Return content of named capture buffer by key or as array
     *
     * @param \Smarty_Internal_Template $_template
     * @param string|null               $name
     *
     * @return string|string[]|null
     */
    public function get_buffer(Smarty_Internal_Template $_template, $name = null)
    {
        if (isset($name)) {
            return isset($this->named_buffer[$name]) ? $this->named_buffer[$name] : null;
        } else {
            return $this->named_buffer;
        }
    }
    /**
     * End render callback
     *
     * @param \Smarty_Internal_Template $_template
     *
     * @throws \SmartyException
     */
    public function end_render(Smarty_Internal_Template $_template)
    {
        if ($this->capture_count) {
            $this->error($_template);
        } else {
            $this->capture_count = array_pop($this->count_stack);
        }
    }
}