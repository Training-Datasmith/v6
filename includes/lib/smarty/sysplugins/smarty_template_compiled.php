<?php

declare (strict_types=1);
/**
 * Smarty Resource Data Object
 * Meta Data Container for Template Files
 *
 * @package    Smarty
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 * @property   string $content compiled content
 */
class Smarty_Template_Compiled extends Smarty_Template_Resource_Base
{
    /**
     * nocache hash
     *
     * @var string|null
     */
    public $nocache_hash = null;
    /**
     * get a Compiled Object of this source
     *
     * @param Smarty_Internal_Template $_template template object
     *
     * @return Smarty_Template_Compiled compiled object
     */
    public static function load($_template)
    {
        $compiled = new Smarty_Template_Compiled();
        if ($_template->source->handler->has_compiled_handler) {
            $_template->source->handler->populate_compiled_filepath($compiled, $_template);
        } else {
            $compiled->populate_compiled_filepath($_template);
        }
        return $compiled;
    }
    /**
     * populate Compiled Object with compiled filepath
     *
     * @param Smarty_Internal_Template $_template template object
     **/
    public function populate_compiled_filepath(Smarty_Internal_Template $_template)
    {
        $source =& $_template->source;
        $smarty =& $_template->smarty;
        $this->filepath = $smarty->get_compile_dir();
        if (isset($_template->compile_id)) {
            $this->filepath .= preg_replace('![^\w]+!', '_', $_template->compile_id) . ($smarty->use_sub_dirs ? DIRECTORY_SEPARATOR : '^');
        }
        // if use_sub_dirs, break file into directories
        if ($smarty->use_sub_dirs) {
            $this->filepath .= $source->uid[0] . $source->uid[1] . DIRECTORY_SEPARATOR . $source->uid[2] . $source->uid[3] . DIRECTORY_SEPARATOR . $source->uid[4] . $source->uid[5] . DIRECTORY_SEPARATOR;
        }
        $this->filepath .= $source->uid . '_';
        if ($source->is_config) {
            $this->filepath .= (int) $smarty->config_read_hidden + (int) $smarty->config_booleanize * 2 + (int) $smarty->config_overwrite * 4;
        } else {
            $this->filepath .= (int) $smarty->merge_compiled_includes + (int) $smarty->escape_html * 2 + ($smarty->merge_compiled_includes && $source->type === 'extends' ? (int) $smarty->extends_recursion * 4 : 0);
        }
        $this->filepath .= '.' . $source->type;
        $basename = $source->handler->get_basename($source);
        if (!empty($basename)) {
            $this->filepath .= '.' . $basename;
        }
        if ($_template->caching) {
            $this->filepath .= '.cache';
        }
        $this->filepath .= '.php';
        $this->timestamp = $this->exists = is_file($this->filepath);
        if ($this->exists) {
            $this->timestamp = filemtime($this->filepath);
        }
    }
    /**
     * render compiled template code
     *
     * @param Smarty_Internal_Template $_template
     *
     * @return string
     * @throws Exception
     */
    public function render(Smarty_Internal_Template $_template)
    {
        // checks if template exists
        if (!$_template->source->exists) {
            $type = $_template->source->is_config ? 'config' : 'template';
            throw new Smarty_Exception("Unable to load {$type} '{$_template->source->type}:{$_template->source->name}'");
        }
        if ($_template->smarty->debugging) {
            if (!isset($_template->smarty->_debug)) {
                $_template->smarty->_debug = new Smarty_Internal_Debug();
            }
            $_template->smarty->_debug->start_render($_template);
        }
        if (!$this->processed) {
            $this->process($_template);
        }
        if (isset($_template->cached)) {
            $_template->cached->file_dependency = array_merge($_template->cached->file_dependency, $this->file_dependency);
        }
        if ($_template->source->handler->uncompiled) {
            $_template->source->handler->render_uncompiled($_template->source, $_template);
        } else {
            $this->get_rendered_template_code($_template);
        }
        if ($_template->caching && $this->has_nocache_code) {
            $_template->cached->hashes[$this->nocache_hash] = true;
        }
        if ($_template->smarty->debugging) {
            $_template->smarty->_debug->end_render($_template);
        }
    }
    /**
     * load compiled template or compile from source
     *
     * @param Smarty_Internal_Template $_smarty_tpl do not change variable name, is used by compiled template
     *
     * @throws Exception
     */
    public function process(Smarty_Internal_Template $_smarty_tpl)
    {
        $source =& $_smarty_tpl->source;
        $smarty =& $_smarty_tpl->smarty;
        if ($source->handler->recompiled) {
            $source->handler->process($_smarty_tpl);
        } elseif (!$source->handler->uncompiled) {
            if (!$this->exists || $smarty->force_compile || $_smarty_tpl->compile_check && $source->get_time_stamp() > $this->get_time_stamp()) {
                $this->compile_template_source($_smarty_tpl);
                $compile_check = $_smarty_tpl->compile_check;
                $_smarty_tpl->compile_check = Smarty::COMPILECHECK_OFF;
                $this->load_compiled_template($_smarty_tpl);
                $_smarty_tpl->compile_check = $compile_check;
            } else {
                $_smarty_tpl->must_compile = true;
                @include $this->filepath;
                if ($_smarty_tpl->must_compile) {
                    $this->compile_template_source($_smarty_tpl);
                    $compile_check = $_smarty_tpl->compile_check;
                    $_smarty_tpl->compile_check = Smarty::COMPILECHECK_OFF;
                    $this->load_compiled_template($_smarty_tpl);
                    $_smarty_tpl->compile_check = $compile_check;
                }
            }
            $_smarty_tpl->_sub_template_register();
            $this->processed = true;
        }
    }
    /**
     * compile template from source
     *
     * @param Smarty_Internal_Template $_template
     *
     * @throws Exception
     */
    public function compile_template_source(Smarty_Internal_Template $_template)
    {
        $this->file_dependency = [];
        $this->includes = [];
        $this->nocache_hash = null;
        $this->unifunc = null;
        // compile locking
        if ($saved_timestamp = !$_template->source->handler->recompiled && is_file($this->filepath)) {
            $saved_timestamp = $this->get_time_stamp();
            touch($this->filepath);
        }
        // compile locking
        try {
            // call compiler
            $_template->load_compiler();
            $this->write($_template, $_template->compiler->compile_template($_template));
        } catch (Exception $e) {
            // restore old timestamp in case of error
            if ($saved_timestamp && is_file($this->filepath)) {
                touch($this->filepath, $saved_timestamp);
            }
            unset($_template->compiler);
            throw $e;
        }
        // release compiler object to free memory
        unset($_template->compiler);
    }
    /**
     * Write compiled code by handler
     *
     * @param Smarty_Internal_Template $_template template object
     * @param string                   $code      compiled code
     *
     * @return bool success
     * @throws \SmartyException
     */
    public function write(Smarty_Internal_Template $_template, $code)
    {
        if (!$_template->source->handler->recompiled) {
            if ($_template->smarty->ext->_write_file->write_file($this->filepath, $code, $_template->smarty) === true) {
                $this->timestamp = $this->exists = is_file($this->filepath);
                if ($this->exists) {
                    $this->timestamp = filemtime($this->filepath);
                    return true;
                }
            }
            return false;
        }
        return true;
    }
    /**
     * Read compiled content from handler
     *
     * @param Smarty_Internal_Template $_template template object
     *
     * @return string content
     */
    public function read(Smarty_Internal_Template $_template)
    {
        if (!$_template->source->handler->recompiled) {
            return file_get_contents($this->filepath);
        }
        return isset($this->content) ? $this->content : false;
    }
    /**
     * Load fresh compiled template by including the PHP file
     * HHVM requires a work around because of a PHP incompatibility
     *
     * @param \Smarty_Internal_Template $_smarty_tpl do not change variable name, is used by compiled template
     */
    private function load_compiled_template(Smarty_Internal_Template $_smarty_tpl)
    {
        if (function_exists('opcache_invalidate') && (!function_exists('ini_get') || strlen(ini_get('opcache.restrict_api')) < 1)) {
            opcache_invalidate($this->filepath, true);
        } elseif (function_exists('apc_compile_file')) {
            apc_compile_file($this->filepath);
        }
        if (defined('HHVM_VERSION')) {
            eval('?>' . file_get_contents($this->filepath));
        } else {
            include $this->filepath;
        }
    }
}