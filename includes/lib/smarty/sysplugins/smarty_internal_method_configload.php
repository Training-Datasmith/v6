<?php

declare (strict_types=1);
/**
 * Smarty Method ConfigLoad
 *
 * Smarty::configLoad() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_config_Load
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * load a config file, optionally load just selected sections
     *
     * @api  Smarty::configLoad()
     * @link https://www.smarty.net/docs/en/api.config.load.tpl
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string                                                  $config_file filename
     * @param mixed                                                   $sections    array of section names, single
     *                                                                             section or null
     *
     * @return \Smarty|\Smarty_Internal_Data|\Smarty_Internal_Template
     * @throws \Exception
     */
    public function config_load(Smarty_Internal_Data $data, $config_file, $sections = null)
    {
        $this->_load_config_file($data, $config_file, $sections, null);
        return $data;
    }
    /**
     * load a config file, optionally load just selected sections
     *
     * @api  Smarty::configLoad()
     * @link https://www.smarty.net/docs/en/api.config.load.tpl
     *
     * @param \Smarty|\Smarty_Internal_Data|\Smarty_Internal_Template $data
     * @param string                                                  $config_file filename
     * @param mixed                                                   $sections    array of section names, single
     *                                                                             section or null
     * @param int                                                     $scope       scope into which config variables
     *                                                                             shall be loaded
     *
     * @throws \Exception
     */
    public function _load_config_file(Smarty_Internal_Data $data, $config_file, $sections = null, $scope = 0)
    {
        /* @var \Smarty $smarty */
        $smarty = $data->_get_smarty_obj();
        /* @var \Smarty_Internal_Template $confObj */
        $conf_obj = new Smarty_Internal_Template($config_file, $smarty, $data, null, null, null, null, true);
        $conf_obj->caching = Smarty::CACHING_OFF;
        $conf_obj->source->config_sections = $sections;
        $conf_obj->source->scope = $scope;
        $conf_obj->compiled = Smarty_Template_Compiled::load($conf_obj);
        $conf_obj->compiled->render($conf_obj);
        if ($data->_is_tpl_obj()) {
            $data->compiled->file_dependency[$conf_obj->source->uid] = [$conf_obj->source->filepath, $conf_obj->source->get_time_stamp(), $conf_obj->source->type];
        }
    }
    /**
     * load config variables into template object
     *
     * @param \Smarty_Internal_Template $tpl
     * @param array                     $new_config_vars
     */
    public function _load_config_vars(Smarty_Internal_Template $tpl, $new_config_vars)
    {
        $this->_assign_config_vars($tpl->parent->config_vars, $tpl, $new_config_vars);
        $tag_scope = $tpl->source->scope;
        if ($tag_scope >= 0) {
            if ($tag_scope === Smarty::SCOPE_LOCAL) {
                $this->_update_var_stack($tpl, $new_config_vars);
                $tag_scope = 0;
                if (!$tpl->scope) {
                    return;
                }
            }
            if ($tpl->parent->_is_tpl_obj() && ($tag_scope || $tpl->parent->scope)) {
                $merged_scope = $tag_scope | $tpl->scope;
                if ($merged_scope) {
                    // update scopes
                    /* @var \Smarty_Internal_Template|\Smarty|\Smarty_Internal_Data $ptr */
                    foreach ($tpl->smarty->ext->_update_scope->_get_affected_scopes($tpl->parent, $merged_scope) as $ptr) {
                        $this->_assign_config_vars($ptr->config_vars, $tpl, $new_config_vars);
                        if ($tag_scope && $ptr->_is_tpl_obj() && isset($tpl->_cache['varStack'])) {
                            $this->_update_var_stack($tpl, $new_config_vars);
                        }
                    }
                }
            }
        }
    }
    /**
     * Assign all config variables in given scope
     *
     * @param array                     $config_vars     config variables in scope
     * @param \Smarty_Internal_Template $tpl
     * @param array                     $new_config_vars loaded config variables
     */
    public function _assign_config_vars(&$config_vars, Smarty_Internal_Template $tpl, $new_config_vars)
    {
        // copy global config vars
        foreach ($new_config_vars['vars'] as $variable => $value) {
            if ($tpl->smarty->config_overwrite || !isset($config_vars[$variable])) {
                $config_vars[$variable] = $value;
            } else {
                $config_vars[$variable] = array_merge((array) $config_vars[$variable], (array) $value);
            }
        }
        // scan sections
        $sections = $tpl->source->config_sections;
        if (!empty($sections)) {
            foreach ((array) $sections as $tpl_section) {
                if (isset($new_config_vars['sections'][$tpl_section])) {
                    foreach ($new_config_vars['sections'][$tpl_section]['vars'] as $variable => $value) {
                        if ($tpl->smarty->config_overwrite || !isset($config_vars[$variable])) {
                            $config_vars[$variable] = $value;
                        } else {
                            $config_vars[$variable] = array_merge((array) $config_vars[$variable], (array) $value);
                        }
                    }
                }
            }
        }
    }
    /**
     * Update config variables in template local variable stack
     *
     * @param \Smarty_Internal_Template $tpl
     * @param array                     $config_vars
     */
    public function _update_var_stack(Smarty_Internal_Template $tpl, $config_vars)
    {
        $i = 0;
        while (isset($tpl->_cache['varStack'][$i])) {
            $this->_assign_config_vars($tpl->_cache['varStack'][$i]['config'], $tpl, $config_vars);
            $i++;
        }
    }
    /**
     * gets  a config variable value
     *
     * @param \Smarty|\Smarty_Internal_Data|\Smarty_Internal_Template $data
     * @param string                                                  $varName the name of the config variable
     * @param bool                                                    $errorEnable
     *
     * @return null|string  the value of the config variable
     */
    public function _get_config_variable(Smarty_Internal_Data $data, $var_name, $error_enable = true)
    {
        $_ptr = $data;
        while ($_ptr !== null) {
            if (isset($_ptr->config_vars[$var_name])) {
                // found it, return it
                return $_ptr->config_vars[$var_name];
            }
            // not found, try at parent
            $_ptr = $_ptr->parent;
        }
        if ($data->smarty->error_unassigned && $error_enable) {
            // force a notice
            $x = ${$var_name};
        }
        return null;
    }
}