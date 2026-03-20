<?php

declare (strict_types=1);
/**
 * Smarty Method ClearAllAssign
 *
 * Smarty::clearAllAssign() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_clear_All_Assign
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * clear all the assigned template variables.
     *
     * @api  Smarty::clearAllAssign()
     * @link https://www.smarty.net/docs/en/api.clear.all.assign.tpl
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function clear_all_assign(Smarty_Internal_Data $data)
    {
        $data->tpl_vars = [];
        return $data;
    }
}