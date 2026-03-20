<?php

declare (strict_types=1);
/**
 * Smarty Method GetConfigVariable
 *
 * Smarty::getConfigVariable() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Config_Variable
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * gets  a config variable value
     *
     * @param \Smarty|\Smarty_Internal_Data|\Smarty_Internal_Template $data
     * @param string                                                  $varName the name of the config variable
     * @param bool                                                    $errorEnable
     *
     * @return null|string  the value of the config variable
     */
    public function get_config_variable(Smarty_Internal_Data $data, $var_name = null, $error_enable = true)
    {
        return $data->ext->config_load->_get_config_variable($data, $var_name, $error_enable);
    }
}