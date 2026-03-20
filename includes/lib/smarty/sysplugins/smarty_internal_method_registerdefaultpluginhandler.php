<?php

declare (strict_types=1);
/**
 * Smarty Method RegisterDefaultPluginHandler
 *
 * Smarty::registerDefaultPluginHandler() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_register_Default_Plugin_Handler
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Registers a default plugin handler
     *
     * @api  Smarty::registerDefaultPluginHandler()
     * @link https://www.smarty.net/docs/en/api.register.default.plugin.handler.tpl
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param callable                                                        $callback class/method name
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws SmartyException              if $callback is not callable
     */
    public function register_default_plugin_handler(Smarty_internal_template_Base $obj, $callback)
    {
        $smarty = $obj->_get_smarty_obj();
        if (is_callable($callback)) {
            $smarty->default_plugin_handler_func = $callback;
        } else {
            throw new Smarty_Exception("Default plugin handler '{$callback}' not callable");
        }
        return $obj;
    }
}