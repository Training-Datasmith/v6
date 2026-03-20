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
 * Base implementation for resource plugins that don't use the compiler
 *
 * @package    Smarty
 * @subpackage TemplateResources
 */
abstract class Smarty_Resource_Uncompiled extends Smarty_Resource
{
    /**
     * Flag that it's an uncompiled resource
     *
     * @var bool
     */
    public $uncompiled = true;
    /**
     * Resource does implement populateCompiledFilepath() method
     *
     * @var bool
     */
    public $has_compiled_handler = true;
    /**
     * populate compiled object with compiled filepath
     *
     * @param Smarty_Template_Compiled $compiled  compiled object
     * @param Smarty_Internal_Template $_template template object
     */
    public function populate_compiled_filepath(Smarty_Template_Compiled $compiled, Smarty_Internal_Template $_template)
    {
        $compiled->filepath = $_template->source->filepath;
        $compiled->timestamp = $_template->source->timestamp;
        $compiled->exists = $_template->source->exists;
        if ($_template->smarty->merge_compiled_includes || $_template->source->handler->check_timestamps()) {
            $compiled->file_dependency[$_template->source->uid] = [$compiled->filepath, $compiled->timestamp, $_template->source->type];
        }
    }
}