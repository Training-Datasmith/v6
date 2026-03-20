<?php

declare (strict_types=1);
/**
 * Smarty Extension handler
 *
 * Load extensions dynamically
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 *
 * Runtime extensions
 * @property   Smarty_Internal_Runtime_CacheModify       $_cacheModify
 * @property   Smarty_Internal_Runtime_CacheResourceFile $_cacheResourceFile
 * @property   Smarty_Internal_Runtime_Capture           $_capture
 * @property   Smarty_Internal_Runtime_CodeFrame         $_codeFrame
 * @property   Smarty_Internal_Runtime_FilterHandler     $_filterHandler
 * @property   Smarty_Internal_Runtime_Foreach           $_foreach
 * @property   Smarty_Internal_Runtime_GetIncludePath    $_getIncludePath
 * @property   Smarty_Internal_Runtime_Make_Nocache      $_make_nocache
 * @property   Smarty_Internal_Runtime_UpdateCache       $_updateCache
 * @property   Smarty_Internal_Runtime_UpdateScope       $_updateScope
 * @property   Smarty_Internal_Runtime_TplFunction       $_tplFunction
 * @property   Smarty_Internal_Runtime_WriteFile         $_writeFile
 *
 * Method extensions
 * @property   Smarty_Internal_Method_GetTemplateVars    $getTemplateVars
 * @property   Smarty_Internal_Method_Append             $append
 * @property   Smarty_Internal_Method_AppendByRef        $appendByRef
 * @property   Smarty_Internal_Method_AssignGlobal       $assignGlobal
 * @property   Smarty_Internal_Method_AssignByRef        $assignByRef
 * @property   Smarty_Internal_Method_LoadFilter         $loadFilter
 * @property   Smarty_Internal_Method_LoadPlugin         $loadPlugin
 * @property   Smarty_Internal_Method_RegisterFilter     $registerFilter
 * @property   Smarty_Internal_Method_RegisterObject     $registerObject
 * @property   Smarty_Internal_Method_RegisterPlugin     $registerPlugin
 * @property   mixed|\Smarty_Template_Cached             configLoad
 */
#[\Allow_Dynamic_Properties]
class Smarty_Internal_Extension_Handler
{
    public $obj_type = null;
    /**
     * Cache for property information from generic getter/setter
     * Preloaded with names which should not use with generic getter/setter
     *
     * @var array
     */
    private $_property_info = ['AutoloadFilters' => 0, 'DefaultModifiers' => 0, 'ConfigVars' => 0, 'DebugTemplate' => 0, 'RegisteredObject' => 0, 'StreamVariable' => 0, 'TemplateVars' => 0, 'Literals' => 'Literals'];
    private $resolved_properties = [];
    /**
     * Call external Method
     *
     * @param \Smarty_Internal_Data $data
     * @param string                $name external method names
     * @param array                 $args argument array
     *
     * @return mixed
     */
    public function _call_external_method(Smarty_Internal_Data $data, $name, $args)
    {
        /* @var Smarty $data ->smarty */
        $smarty = isset($data->smarty) ? $data->smarty : $data;
        if (!isset($smarty->ext->{$name})) {
            if (preg_match('/^((set|get)|(.*?))([A-Z].*)$/', $name, $match)) {
                $basename = $this->upper_case($match[4]);
                if (!isset($smarty->ext->{$basename}) && isset($this->_property_info[$basename]) && is_string($this->_property_info[$basename])) {
                    $class = 'Smarty_Internal_Method_' . $this->_property_info[$basename];
                    if (class_exists($class)) {
                        $class_obj = new $class();
                        $methodes = get_class_methods($class_obj);
                        foreach ($methodes as $method) {
                            $smarty->ext->{$method} = $class_obj;
                        }
                    }
                }
                if (!empty($match[2]) && !isset($smarty->ext->{$name})) {
                    $class = 'Smarty_Internal_Method_' . $this->upper_case($name);
                    if (!class_exists($class)) {
                        $obj_type = $data->_obj_type;
                        $property_type = false;
                        if (!isset($this->resolved_properties[$match[0]][$obj_type])) {
                            $property = $this->resolved_properties['property'][$basename] ?? $this->resolved_properties['property'][$basename] = smarty_strtolower_ascii(join('_', preg_split('/([A-Z][^A-Z]*)/', $basename, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE)));
                            if ($property !== false) {
                                if (property_exists($data, $property)) {
                                    $property_type = $this->resolved_properties[$match[0]][$obj_type] = 1;
                                } elseif (property_exists($smarty, $property)) {
                                    $property_type = $this->resolved_properties[$match[0]][$obj_type] = 2;
                                } else {
                                    $this->resolved_properties['property'][$basename] = $property = false;
                                }
                            }
                        } else {
                            $property_type = $this->resolved_properties[$match[0]][$obj_type];
                            $property = $this->resolved_properties['property'][$basename];
                        }
                        if ($property_type) {
                            $obj = $property_type === 1 ? $data : $smarty;
                            if ($match[2] === 'get') {
                                return $obj->{$property};
                            } elseif ($match[2] === 'set') {
                                return $obj->{$property} = $args[0];
                            }
                        }
                    }
                }
            }
        }
        $callback = [$smarty->ext->{$name}, $name];
        array_unshift($args, $data);
        if (isset($callback) && $callback[0]->obj_map | $data->_obj_type) {
            return call_user_func_array($callback, $args);
        }
        return call_user_func_array([new Smarty_Internal_Undefined(), $name], $args);
    }
    /**
     * Make first character of name parts upper case
     *
     * @param string $name
     *
     * @return string
     */
    public function upper_case($name)
    {
        $_name = explode('_', $name);
        $_name = array_map('smarty_ucfirst_ascii', $_name);
        return implode('_', $_name);
    }
    /**
     * get extension object
     *
     * @param string $property_name property name
     *
     * @return mixed|Smarty_Template_Cached
     */
    public function __get($property_name)
    {
        // object properties of runtime template extensions will start with '_'
        if ($property_name[0] === '_') {
            $class = 'Smarty_Internal_Runtime' . $this->upper_case($property_name);
        } else {
            $class = 'Smarty_Internal_Method_' . $this->upper_case($property_name);
        }
        if (!class_exists($class)) {
            return $this->{$property_name} = new Smarty_Internal_Undefined($class);
        }
        return $this->{$property_name} = new $class();
    }
    /**
     * set extension property
     *
     * @param string $property_name property name
     * @param mixed  $value         value
     *
     */
    public function __set($property_name, $value)
    {
        $this->{$property_name} = $value;
    }
    /**
     * Call error handler for undefined method
     *
     * @param string $name unknown method-name
     * @param array  $args argument array
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        return call_user_func_array([new Smarty_Internal_Undefined(), $name], [$this]);
    }
}