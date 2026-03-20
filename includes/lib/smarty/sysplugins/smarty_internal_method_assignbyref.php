<?php

declare (strict_types=1);
/**
 * Smarty Method AssignByRef
 *
 * Smarty::assignByRef() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_assign_By_Ref
{
    /**
     * assigns values to template variables by reference
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string                                                  $tpl_var the template variable name
     * @param                                                         $value
     * @param boolean                                                 $nocache if true any output of this variable will
     *                                                                         be not cached
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function assign_by_ref(Smarty_Internal_Data $data, $tpl_var, &$value, $nocache)
    {
        if ($tpl_var !== '') {
            $data->tpl_vars[$tpl_var] = new Smarty_Variable(null, $nocache);
            $data->tpl_vars[$tpl_var]->value =& $value;
            if ($data->_is_tpl_obj() && $data->scope) {
                $data->ext->_update_scope->_update_scope($data, $tpl_var);
            }
        }
        return $data;
    }
}