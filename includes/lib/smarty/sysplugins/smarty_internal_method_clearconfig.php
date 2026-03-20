<?php

declare (strict_types=1);
/**
 * Smarty Method ClearConfig
 *
 * Smarty::clearConfig() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_clear_Config
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * clear a single or all config variables
     *
     * @api  Smarty::clearConfig()
     * @link https://www.smarty.net/docs/en/api.clear.config.tpl
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string|null                                             $name variable name or null
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function clear_config(Smarty_Internal_Data $data, $name = null)
    {
        if (isset($name)) {
            unset($data->config_vars[$name]);
        } else {
            $data->config_vars = [];
        }
        return $data;
    }
}