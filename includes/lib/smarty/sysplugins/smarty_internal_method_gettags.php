<?php

declare (strict_types=1);
/**
 * Smarty Method GetTags
 *
 * Smarty::getTags() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Tags
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Return array of tag/attributes of all tags used by an template
     *
     * @api  Smarty::getTags()
     * @link https://www.smarty.net/docs/en/api.get.tags.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param null|string|Smarty_Internal_Template                            $template
     *
     * @return array of tag/attributes
     * @throws \Exception
     * @throws \SmartyException
     */
    public function get_tags(Smarty_internal_template_Base $obj, $template = null)
    {
        /* @var Smarty $smarty */
        $smarty = $obj->_get_smarty_obj();
        if ($obj->_is_tpl_obj() && !isset($template)) {
            $tpl = clone $obj;
        } elseif (isset($template) && $template->_is_tpl_obj()) {
            $tpl = clone $template;
        } elseif (isset($template) && is_string($template)) {
            /* @var Smarty_Internal_Template $tpl */
            $tpl = new $smarty->template_class($template, $smarty);
            // checks if template exists
            if (!$tpl->source->exists) {
                throw new Smarty_Exception("Unable to load template {$tpl->source->type} '{$tpl->source->name}'");
            }
        }
        if (isset($tpl)) {
            $tpl->smarty = clone $tpl->smarty;
            $tpl->smarty->_cache['get_used_tags'] = true;
            $tpl->_cache['used_tags'] = [];
            $tpl->smarty->merge_compiled_includes = false;
            $tpl->smarty->disable_security();
            $tpl->caching = Smarty::CACHING_OFF;
            $tpl->load_compiler();
            $tpl->compiler->compile_template($tpl);
            return $tpl->_cache['used_tags'];
        }
        throw new Smarty_Exception('Missing template specification');
    }
}