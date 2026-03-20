<?php

declare (strict_types=1);
/**
 * Smarty Method GetTemplateVars
 *
 * Smarty::getTemplateVars() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Template_Vars
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * Returns a single or all template variables
     *
     * @api  Smarty::getTemplateVars()
     * @link https://www.smarty.net/docs/en/api.get.template.vars.tpl
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string                                                  $varName       variable name or null
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $_ptr          optional pointer to data object
     * @param bool                                                    $searchParents include parent templates?
     *
     * @return mixed variable value or or array of variables
     */
    public function get_template_vars(Smarty_Internal_Data $data, $var_name = null, Smarty_Internal_Data $_ptr = null, $search_parents = true)
    {
        if (isset($var_name)) {
            $_var = $this->_get_variable($data, $var_name, $_ptr, $search_parents, false);
            if (is_object($_var)) {
                return $_var->value;
            } else {
                return null;
            }
        } else {
            $_result = [];
            if ($_ptr === null) {
                $_ptr = $data;
            }
            while ($_ptr !== null) {
                foreach ($_ptr->tpl_vars as $key => $var) {
                    if (!array_key_exists($key, $_result)) {
                        $_result[$key] = $var->value;
                    }
                }
                // not found, try at parent
                if ($search_parents && isset($_ptr->parent)) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }
            if ($search_parents && isset(Smarty::$global_tpl_vars)) {
                foreach (Smarty::$global_tpl_vars as $key => $var) {
                    if (!array_key_exists($key, $_result)) {
                        $_result[$key] = $var->value;
                    }
                }
            }
            return $_result;
        }
    }
    /**
     * gets the object of a Smarty variable
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string                                                  $varName       the name of the Smarty variable
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $_ptr          optional pointer to data object
     * @param bool                                                    $searchParents search also in parent data
     * @param bool                                                    $errorEnable
     *
     * @return \Smarty_Variable
     */
    public function _get_variable(Smarty_Internal_Data $data, $var_name, Smarty_Internal_Data $_ptr = null, $search_parents = true, $error_enable = true)
    {
        if ($_ptr === null) {
            $_ptr = $data;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->tpl_vars[$var_name])) {
                // found it, return it
                return $_ptr->tpl_vars[$var_name];
            }
            // not found, try at parent
            if ($search_parents && isset($_ptr->parent)) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset(Smarty::$global_tpl_vars[$var_name])) {
            // found it, return it
            return Smarty::$global_tpl_vars[$var_name];
        }
        if ($error_enable && $data->_get_smarty_obj()->error_unassigned) {
            // force a notice
            $x = ${$var_name};
        }
        return new Smarty_Undefined_Variable();
    }
}