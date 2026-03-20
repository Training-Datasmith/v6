<?php

declare (strict_types=1);
/**
 * Smarty Method GetGlobal
 *
 * Smarty::getGlobal() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Global
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * Returns a single or all global  variables
     *
     * @api Smarty::getGlobal()
     *
     * @param \Smarty_Internal_Data $data
     * @param string                $varName variable name or null
     *
     * @return string|array variable value or or array of variables
     */
    public function get_global(Smarty_Internal_Data $data, $var_name = null)
    {
        if (isset($var_name)) {
            if (isset(Smarty::$global_tpl_vars[$var_name])) {
                return Smarty::$global_tpl_vars[$var_name]->value;
            } else {
                return '';
            }
        } else {
            $_result = [];
            foreach (Smarty::$global_tpl_vars as $key => $var) {
                $_result[$key] = $var->value;
            }
            return $_result;
        }
    }
}