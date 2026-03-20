<?php

declare (strict_types=1);
/**
 * Smarty Method ClearAssign
 *
 * Smarty::clearAssign() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_clear_Assign
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * clear the given assigned template variable(s).
     *
     * @api  Smarty::clearAssign()
     * @link https://www.smarty.net/docs/en/api.clear.assign.tpl
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string|array                                            $tpl_var the template variable(s) to clear
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function clear_assign(Smarty_Internal_Data $data, $tpl_var)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $curr_var) {
                unset($data->tpl_vars[$curr_var]);
            }
        } else {
            unset($data->tpl_vars[$tpl_var]);
        }
        return $data;
    }
}