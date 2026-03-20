<?php

declare (strict_types=1);
/**
 * Runtime Extension updateScope
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 **/
class Smarty_internal_runtime_update_Scope
{
    /**
     * Update new assigned template or config variable in other effected scopes
     *
     * @param Smarty_Internal_Template $tpl      data object
     * @param string|null              $varName  variable name
     * @param int                      $tagScope tag scope to which bubble up variable value
     */
    public function _update_scope(Smarty_Internal_Template $tpl, $var_name, $tag_scope = 0)
    {
        if ($tag_scope) {
            $this->_update_var_stack($tpl, $var_name);
            $tag_scope = $tag_scope & ~Smarty::SCOPE_LOCAL;
            if (!$tpl->scope && !$tag_scope) {
                return;
            }
        }
        $merged_scope = $tag_scope | $tpl->scope;
        if ($merged_scope) {
            if ($merged_scope & Smarty::SCOPE_GLOBAL && $var_name) {
                Smarty::$global_tpl_vars[$var_name] = $tpl->tpl_vars[$var_name];
            }
            // update scopes
            foreach ($this->_get_affected_scopes($tpl, $merged_scope) as $ptr) {
                $this->_update_variable_in_other_scope($ptr->tpl_vars, $tpl, $var_name);
                if ($tag_scope && $ptr->_is_tpl_obj() && isset($tpl->_cache['varStack'])) {
                    $this->_update_var_stack($ptr, $var_name);
                }
            }
        }
    }
    /**
     * Get array of objects which needs to be updated  by given scope value
     *
     * @param Smarty_Internal_Template $tpl
     * @param int                      $mergedScope merged tag and template scope to which bubble up variable value
     *
     * @return array
     */
    public function _get_affected_scopes(Smarty_Internal_Template $tpl, $merged_scope)
    {
        $_stack = [];
        $ptr = $tpl->parent;
        if ($merged_scope && isset($ptr) && $ptr->_is_tpl_obj()) {
            $_stack[] = $ptr;
            $merged_scope = $merged_scope & ~Smarty::SCOPE_PARENT;
            if (!$merged_scope) {
                // only parent was set, we are done
                return $_stack;
            }
            $ptr = $ptr->parent;
        }
        while (isset($ptr) && $ptr->_is_tpl_obj()) {
            $_stack[] = $ptr;
            $ptr = $ptr->parent;
        }
        if ($merged_scope & Smarty::SCOPE_SMARTY) {
            if (isset($tpl->smarty)) {
                $_stack[] = $tpl->smarty;
            }
        } elseif ($merged_scope & Smarty::SCOPE_ROOT) {
            while (isset($ptr)) {
                if (!$ptr->_is_tpl_obj()) {
                    $_stack[] = $ptr;
                    break;
                }
                $ptr = $ptr->parent;
            }
        }
        return $_stack;
    }
    /**
     * Update variable in other scope
     *
     * @param array                     $tpl_vars template variable array
     * @param \Smarty_Internal_Template $from
     * @param string                    $varName  variable name
     */
    public function _update_variable_in_other_scope(&$tpl_vars, Smarty_Internal_Template $from, $var_name)
    {
        if (!isset($tpl_vars[$var_name])) {
            $tpl_vars[$var_name] = clone $from->tpl_vars[$var_name];
        } else {
            $tpl_vars[$var_name] = clone $tpl_vars[$var_name];
            $tpl_vars[$var_name]->value = $from->tpl_vars[$var_name]->value;
        }
    }
    /**
     * Update variable in template local variable stack
     *
     * @param \Smarty_Internal_Template $tpl
     * @param string|null               $varName variable name or null for config variables
     */
    public function _update_var_stack(Smarty_Internal_Template $tpl, $var_name)
    {
        $i = 0;
        while (isset($tpl->_cache['varStack'][$i])) {
            $this->_update_variable_in_other_scope($tpl->_cache['varStack'][$i]['tpl'], $tpl, $var_name);
            $i++;
        }
    }
}