<?php

declare (strict_types=1);
/**
 * TplFunction Runtime Methods callTemplateFunction
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 **/
class Smarty_internal_runtime_tpl_Function
{
    /**
     * Call template function
     *
     * @param \Smarty_Internal_Template $tpl     template object
     * @param string                    $name    template function name
     * @param array                     $params  parameter array
     * @param bool                      $nocache true if called nocache
     *
     * @throws \SmartyException
     */
    public function call_template_function(Smarty_Internal_Template $tpl, $name, $params, $nocache)
    {
        $func_param = isset($tpl->tpl_functions[$name]) ? $tpl->tpl_functions[$name] : (isset($tpl->smarty->tpl_functions[$name]) ? $tpl->smarty->tpl_functions[$name] : null);
        if (isset($func_param)) {
            if (!$tpl->caching || $tpl->caching && $nocache) {
                $function = $func_param['call_name'];
            } else if (isset($func_param['call_name_caching'])) {
                $function = $func_param['call_name_caching'];
            } else {
                $function = $func_param['call_name'];
            }
            if (function_exists($function)) {
                $this->save_template_variables($tpl, $name);
                $function($tpl, $params);
                $this->restore_template_variables($tpl, $name);
                return;
            }
            // try to load template function dynamically
            if ($this->add_tpl_func_to_cache($tpl, $name, $function)) {
                $this->save_template_variables($tpl, $name);
                $function($tpl, $params);
                $this->restore_template_variables($tpl, $name);
                return;
            }
        }
        throw new Smarty_Exception("Unable to find template function '{$name}'");
    }
    /**
     * Register template functions defined by template
     *
     * @param \Smarty|\Smarty_Internal_Template|\Smarty_Internal_TemplateBase $obj
     * @param array                                                           $tplFunctions source information array of
     *                                                                                      template functions defined
     *                                                                                      in template
     * @param bool                                                            $override     if true replace existing
     *                                                                                      functions with same name
     */
    public function register_tpl_functions(Smarty_internal_template_Base $obj, $tpl_functions, $override = true)
    {
        $obj->tpl_functions = $override ? array_merge($obj->tpl_functions, $tpl_functions) : array_merge($tpl_functions, $obj->tpl_functions);
        // make sure that the template functions are known in parent templates
        if ($obj->_is_sub_tpl()) {
            $obj->smarty->ext->_tpl_function->register_tpl_functions($obj->parent, $tpl_functions, false);
        } else {
            $obj->smarty->tpl_functions = $override ? array_merge($obj->smarty->tpl_functions, $tpl_functions) : array_merge($tpl_functions, $obj->smarty->tpl_functions);
        }
    }
    /**
     * Return source parameter array for single or all template functions
     *
     * @param \Smarty_Internal_Template $tpl  template object
     * @param null|string               $name template function name
     *
     * @return array|bool|mixed
     */
    public function get_tpl_function(Smarty_Internal_Template $tpl, $name = null)
    {
        if (isset($name)) {
            return isset($tpl->tpl_functions[$name]) ? $tpl->tpl_functions[$name] : (isset($tpl->smarty->tpl_functions[$name]) ? $tpl->smarty->tpl_functions[$name] : false);
        } else {
            return empty($tpl->tpl_functions) ? $tpl->smarty->tpl_functions : $tpl->tpl_functions;
        }
    }
    /**
     * Add template function to cache file for nocache calls
     *
     * @param Smarty_Internal_Template $tpl
     * @param string                   $_name     template function name
     * @param string                   $_function PHP function name
     *
     * @return bool
     */
    public function add_tpl_func_to_cache(Smarty_Internal_Template $tpl, $_name, $_function)
    {
        $func_param = $tpl->tpl_functions[$_name];
        if (is_file($func_param['compiled_filepath'])) {
            // read compiled file
            $code = file_get_contents($func_param['compiled_filepath']);
            // grab template function
            if (preg_match("/\\/\\* {$_function} \\*\\/([\\S\\s]*?)\\/\\*\\/ {$_function} \\*\\//", $code, $match)) {
                // grab source info from file dependency
                preg_match("/\\s*'{$func_param['uid']}'([\\S\\s]*?)\\),/", $code, $match1);
                unset($code);
                // make PHP function known
                eval($match[0]);
                if (function_exists($_function)) {
                    // search cache file template
                    $tpl_ptr = $tpl;
                    while (!isset($tpl_ptr->cached) && isset($tpl_ptr->parent)) {
                        $tpl_ptr = $tpl_ptr->parent;
                    }
                    // add template function code to cache file
                    if (isset($tpl_ptr->cached)) {
                        $content = $tpl_ptr->cached->read($tpl_ptr);
                        if ($content) {
                            // check if we must update file dependency
                            if (!preg_match("/'{$func_param['uid']}'(.*?)'nocache_hash'/", $content, $match2)) {
                                $content = preg_replace("/('file_dependency'(.*?)\\()/", "\\1{$match1[0]}", $content);
                            }
                            $tpl_ptr->smarty->ext->_update_cache->write($tpl_ptr, preg_replace('/\s*\?>\s*$/', "\n", $content) . "\n" . preg_replace(['/^\s*<\?php\s+/', '/\s*\?>\s*$/'], "\n", $match[0]));
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Save current template variables on stack
     *
     * @param \Smarty_Internal_Template $tpl
     * @param string                    $name stack name
     */
    public function save_template_variables(Smarty_Internal_Template $tpl, $name)
    {
        $tpl->_cache['varStack'][] = ['tpl' => $tpl->tpl_vars, 'config' => $tpl->config_vars, 'name' => "_tplFunction_{$name}"];
    }
    /**
     * Restore saved variables into template objects
     *
     * @param \Smarty_Internal_Template $tpl
     * @param string                    $name stack name
     */
    public function restore_template_variables(Smarty_Internal_Template $tpl, $name)
    {
        if (isset($tpl->_cache['varStack'])) {
            $vars = array_pop($tpl->_cache['varStack']);
            $tpl->tpl_vars = $vars['tpl'];
            $tpl->config_vars = $vars['config'];
        }
    }
}