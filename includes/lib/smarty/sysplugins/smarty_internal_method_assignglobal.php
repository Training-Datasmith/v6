<?php

declare (strict_types=1);
/**
 * Smarty Method AssignGlobal
 *
 * Smarty::assignGlobal() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_assign_Global
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * assigns a global Smarty variable
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string                                                  $varName the global variable name
     * @param mixed                                                   $value   the value to assign
     * @param boolean                                                 $nocache if true any output of this variable will
     *                                                                         be not cached
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function assign_global(Smarty_Internal_Data $data, $var_name, $value = null, $nocache = false)
    {
        if ($var_name !== '') {
            Smarty::$global_tpl_vars[$var_name] = new Smarty_Variable($value, $nocache);
            $ptr = $data;
            while ($ptr->_is_tpl_obj()) {
                $ptr->tpl_vars[$var_name] = clone Smarty::$global_tpl_vars[$var_name];
                $ptr = $ptr->parent;
            }
        }
        return $data;
    }
}