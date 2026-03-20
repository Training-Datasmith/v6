<?php

declare (strict_types=1);
/**
 * Smarty Method RegisterDefaultConfigHandler
 *
 * Smarty::registerDefaultConfigHandler() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_register_Default_Config_Handler
{
    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $obj_map = 3;
    /**
     * Register config default handler
     *
     * @api Smarty::registerDefaultConfigHandler()
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     * @param callable                                                        $callback class/method name
     *
     * @return \Smarty|\Smarty_Internal_Template
     * @throws SmartyException              if $callback is not callable
     */
    public function register_default_config_handler(Smarty_internal_template_Base $obj, $callback)
    {
        $smarty = $obj->_get_smarty_obj();
        if (is_callable($callback)) {
            $smarty->default_config_handler_func = $callback;
        } else {
            throw new Smarty_Exception('Default config handler not callable');
        }
        return $obj;
    }
}