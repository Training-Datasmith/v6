<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Data
 * This file contains the basic classes and methods for template and variable creation
 *
 * @package    Smarty
 * @subpackage Template
 * @author     Uwe Tews
 */
/**
 * Base class with template and variable methods
 *
 * @package    Smarty
 * @subpackage Template
 *
 * @property int    $scope
 * @property Smarty $smarty
 * The following methods will be dynamically loaded by the extension handler when they are called.
 * They are located in a corresponding Smarty_Internal_Method_xxxx class
 *
 * @method mixed _getConfigVariable(string $varName, bool $errorEnable = true)
 * @method mixed getConfigVariable(string $varName, bool $errorEnable = true)
 * @method mixed getConfigVars(string $varName = null, bool $searchParents = true)
 * @method mixed getGlobal(string $varName = null)
 * @method mixed getStreamVariable(string $variable)
 * @method Smarty_Internal_Data clearAssign(mixed $tpl_var)
 * @method Smarty_Internal_Data clearAllAssign()
 * @method Smarty_Internal_Data clearConfig(string $varName = null)
 * @method Smarty_Internal_Data configLoad(string $config_file, mixed $sections = null, string $scope = 'local')
 */
abstract class Smarty_Internal_Data
{
    /**
     * This object type (Smarty = 1, template = 2, data = 4)
     *
     * @var int
     */
    public $_obj_type = 4;
    /**
     * name of class used for templates
     *
     * @var string
     */
    public $template_class = 'Smarty_Internal_Template';
    /**
     * template variables
     *
     * @var Smarty_Variable[]
     */
    public $tpl_vars = [];
    /**
     * parent template (if any)
     *
     * @var Smarty|Smarty_Internal_Template|Smarty_Data
     */
    public $parent = null;
    /**
     * configuration settings
     *
     * @var string[]
     */
    public $config_vars = [];
    /**
     * extension handler
     *
     * @var Smarty_Internal_Extension_Handler
     */
    public $ext = null;
    /**
     * Smarty_Internal_Data constructor.
     *
     * Install extension handler
     */
    public function __construct()
    {
        $this->ext = new Smarty_Internal_Extension_Handler();
        $this->ext->obj_type = $this->_obj_type;
    }
    /**
     * assigns a Smarty variable
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed        $value   the value to assign
     * @param boolean      $nocache if true any output of this variable will be not cached
     *
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for
     *                              chaining
     */
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $_key => $_val) {
                $this->assign($_key, $_val, $nocache);
            }
        } else if ($tpl_var !== '') {
            if ($this->_obj_type === 2) {
                /**
                 *
                 *
                 * @var Smarty_Internal_Template $this
                 */
                $this->_assign_in_scope($tpl_var, $value, $nocache);
            } else {
                $this->tpl_vars[$tpl_var] = new Smarty_Variable($value, $nocache);
            }
        }
        return $this;
    }
    /**
     * appends values to template variables
     *
     * @api  Smarty::append()
     * @link https://www.smarty.net/docs/en/api.append.tpl
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed        $value   the value to append
     * @param bool         $merge   flag if array elements shall be merged
     * @param bool         $nocache if true any output of this variable will
     *                              be not cached
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function append($tpl_var, $value = null, $merge = false, $nocache = false)
    {
        return $this->ext->append->append($this, $tpl_var, $value, $merge, $nocache);
    }
    /**
     * assigns a global Smarty variable
     *
     * @param string  $varName the global variable name
     * @param mixed   $value   the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function assign_global($var_name, $value = null, $nocache = false)
    {
        return $this->ext->assign_global->assign_global($this, $var_name, $value, $nocache);
    }
    /**
     * appends values to template variables by reference
     *
     * @param string  $tpl_var the template variable name
     * @param mixed   &$value  the referenced value to append
     * @param boolean $merge   flag if array elements shall be merged
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function append_by_ref($tpl_var, &$value, $merge = false)
    {
        return $this->ext->append_by_ref->append_by_ref($this, $tpl_var, $value, $merge);
    }
    /**
     * assigns values to template variables by reference
     *
     * @param string  $tpl_var the template variable name
     * @param         $value
     * @param boolean $nocache if true any output of this variable will be not cached
     *
     * @return \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty
     */
    public function assign_by_ref($tpl_var, &$value, $nocache = false)
    {
        return $this->ext->assign_by_ref->assign_by_ref($this, $tpl_var, $value, $nocache);
    }
    /**
     * Returns a single or all template variables
     *
     * @api  Smarty::getTemplateVars()
     * @link https://www.smarty.net/docs/en/api.get.template.vars.tpl
     *
     * @param string                                                  $varName       variable name or null
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $_ptr          optional pointer to data object
     * @param bool                                                    $searchParents include parent templates?
     *
     * @return mixed variable value or or array of variables
     */
    public function get_template_vars($var_name = null, Smarty_Internal_Data $_ptr = null, $search_parents = true)
    {
        return $this->ext->get_template_vars->get_template_vars($this, $var_name, $_ptr, $search_parents);
    }
    /**
     * Follow the parent chain an merge template and config variables
     *
     * @param \Smarty_Internal_Data|null $data
     */
    public function _merge_vars(Smarty_Internal_Data $data = null)
    {
        if (isset($data)) {
            if (!empty($this->tpl_vars)) {
                $data->tpl_vars = array_merge($this->tpl_vars, $data->tpl_vars);
            }
            if (!empty($this->config_vars)) {
                $data->config_vars = array_merge($this->config_vars, $data->config_vars);
            }
        } else {
            $data = $this;
        }
        if (isset($this->parent)) {
            $this->parent->_merge_vars($data);
        }
    }
    /**
     * Return true if this instance is a Data obj
     *
     * @return bool
     */
    public function _is_data_obj()
    {
        return $this->_obj_type === 4;
    }
    /**
     * Return true if this instance is a template obj
     *
     * @return bool
     */
    public function _is_tpl_obj()
    {
        return $this->_obj_type === 2;
    }
    /**
     * Return true if this instance is a Smarty obj
     *
     * @return bool
     */
    public function _is_smarty_obj()
    {
        return $this->_obj_type === 1;
    }
    /**
     * Get Smarty object
     *
     * @return Smarty
     */
    public function _get_smarty_obj()
    {
        return $this->smarty;
    }
    /**
     * Handle unknown class methods
     *
     * @param string $name unknown method-name
     * @param array  $args argument array
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        return $this->ext->_call_external_method($this, $name, $args);
    }
}