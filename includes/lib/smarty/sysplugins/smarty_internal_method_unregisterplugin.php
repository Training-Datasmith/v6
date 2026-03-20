<?php

declare (strict_types=1);
/**
 * Smarty Method UnregisterPlugin
 *
 * Smarty::unregisterPlugin() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_unregister_Plugin
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Registers plugin to be used in templates
     *
     * @api  Smarty::unregisterPlugin()
     * @link https://www.smarty.net/docs/en/api.unregister.plugin.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param string                                                          $type plugin type
     * @param string                                                          $name name of template tag
     *
     * @return \Smarty|\Smarty_Internal_Template
     */
    public function unregister_plugin(Smarty_internal_template_Base $obj, $type, $name)
    {
        $smarty = $obj->_get_smarty_obj();
        if (isset($smarty->registered_plugins[$type][$name])) {
            unset($smarty->registered_plugins[$type][$name]);
        }
        return $obj;
    }
}