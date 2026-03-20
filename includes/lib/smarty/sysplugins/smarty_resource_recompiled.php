<?php

declare (strict_types=1);
/**
 * Smarty Resource Plugin
 *
 * @package    Smarty
 * @subpackage TemplateResources
 * @author     Rodney Rehm
 */
/**
 * Smarty Resource Plugin
 * Base implementation for resource plugins that don't compile cache
 *
 * @package    Smarty
 * @subpackage TemplateResources
 */
abstract class Smarty_Resource_Recompiled extends Smarty_Resource
{
    /**
     * Flag that it's an recompiled resource
     *
     * @var bool
     */
    public $recompiled = true;
    /**
     * Resource does implement populateCompiledFilepath() method
     *
     * @var bool
     */
    public $has_compiled_handler = true;
    /**
     * compile template from source
     *
     * @param Smarty_Internal_Template $_smarty_tpl do not change variable name, is used by compiled template
     *
     * @throws Exception
     */
    public function process(Smarty_Internal_Template $_smarty_tpl)
    {
        $compiled =& $_smarty_tpl->compiled;
        $compiled->file_dependency = [];
        $compiled->includes = [];
        $compiled->nocache_hash = null;
        $compiled->unifunc = null;
        $level = ob_get_level();
        ob_start();
        $_smarty_tpl->load_compiler();
        // call compiler
        try {
            eval('?>' . $_smarty_tpl->compiler->compile_template($_smarty_tpl));
        } catch (Exception $e) {
            unset($_smarty_tpl->compiler);
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
        // release compiler object to free memory
        unset($_smarty_tpl->compiler);
        ob_get_clean();
        $compiled->timestamp = time();
        $compiled->exists = true;
    }
    /**
     * populate Compiled Object with compiled filepath
     *
     * @param Smarty_Template_Compiled $compiled  compiled object
     * @param Smarty_Internal_Template $_template template object
     *
     * @return void
     */
    public function populate_compiled_filepath(Smarty_Template_Compiled $compiled, Smarty_Internal_Template $_template)
    {
        $compiled->filepath = false;
        $compiled->timestamp = false;
        $compiled->exists = false;
    }
    /*
     * Disable timestamp checks for recompiled resource.
     *
     * @return bool
     */
    /**
     * @return bool
     */
    public function check_timestamps()
    {
        return false;
    }
}