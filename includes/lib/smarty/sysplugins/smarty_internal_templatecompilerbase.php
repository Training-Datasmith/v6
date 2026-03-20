<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Smarty Template Compiler Base
 * This file contains the basic classes and methods for compiling Smarty templates with lexer/parser
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Uwe Tews
 */
/**
 * Main abstract compiler class
 *
 * @package    Smarty
 * @subpackage Compiler
 *
 * @property Smarty_Internal_SmartyTemplateCompiler $prefixCompiledCode  = ''
 * @property Smarty_Internal_SmartyTemplateCompiler $postfixCompiledCode = ''
 * @method   registerPostCompileCallback($callback, $parameter = array(), $key = null, $replace = false)
 * @method   unregisterPostCompileCallback($key)
 */
abstract class Smarty_internal_template_Compiler_Base
{
    /**
     * compile tag objects cache
     *
     * @var array
     */
    public static $_tag_objects = [];
    /**
     * counter for prefix variable number
     *
     * @var int
     */
    public static $prefix_variable_number = 0;
    /**
     * Smarty object
     *
     * @var Smarty
     */
    public $smarty = null;
    /**
     * Parser object
     *
     * @var Smarty_Internal_Templateparser
     */
    public $parser = null;
    /**
     * hash for nocache sections
     *
     * @var mixed
     */
    public $nocache_hash = null;
    /**
     * suppress generation of nocache code
     *
     * @var bool
     */
    public $suppress_nocache_processing = false;
    /**
     * caching enabled (copied from template object)
     *
     * @var int
     */
    public $caching = 0;
    /**
     * tag stack
     *
     * @var array
     */
    public $_tag_stack = [];
    /**
     * tag stack count
     *
     * @var array
     */
    public $_tag_stack_count = [];
    /**
     * Plugins used by template
     *
     * @var array
     */
    public $required_plugins = ['compiled' => [], 'nocache' => []];
    /**
     * Required plugins stack
     *
     * @var array
     */
    public $required_plugins_stack = [];
    /**
     * current template
     *
     * @var Smarty_Internal_Template
     */
    public $template = null;
    /**
     * merged included sub template data
     *
     * @var array
     */
    public $merged_sub_templates_data = [];
    /**
     * merged sub template code
     *
     * @var array
     */
    public $merged_sub_templates_code = [];
    /**
     * collected template properties during compilation
     *
     * @var array
     */
    public $template_properties = [];
    /**
     * source line offset for error messages
     *
     * @var int
     */
    public $trace_line_offset = 0;
    /**
     * trace uid
     *
     * @var string
     */
    public $trace_uid = '';
    /**
     * trace file path
     *
     * @var string
     */
    public $trace_filepath = '';
    /**
     * stack for tracing file and line of nested {block} tags
     *
     * @var array
     */
    public $trace_stack = [];
    /**
     * plugins loaded by default plugin handler
     *
     * @var array
     */
    public $default_handler_plugins = [];
    /**
     * saved preprocessed modifier list
     *
     * @var mixed
     */
    public $default_modifier_list = null;
    /**
     * force compilation of complete template as nocache
     *
     * @var boolean
     */
    public $force_nocache = false;
    /**
     * flag if compiled template file shall we written
     *
     * @var bool
     */
    public $write_compiled_code = true;
    /**
     * Template functions
     *
     * @var array
     */
    public $tpl_function = [];
    /**
     * called sub functions from template function
     *
     * @var array
     */
    public $called_functions = [];
    /**
     * compiled template or block function code
     *
     * @var string
     */
    public $block_or_function_code = '';
    /**
     * flags for used modifier plugins
     *
     * @var array
     */
    public $modifier_plugins = [];
    /**
     * type of already compiled modifier
     *
     * @var array
     */
    public $known_modifier_type = [];
    /**
     * parent compiler object for merged subtemplates and template functions
     *
     * @var Smarty_Internal_TemplateCompilerBase
     */
    public $parent_compiler = null;
    /**
     * Flag true when compiling nocache section
     *
     * @var bool
     */
    public $nocache = false;
    /**
     * Flag true when tag is compiled as nocache
     *
     * @var bool
     */
    public $tag_nocache = false;
    /**
     * Compiled tag prefix code
     *
     * @var array
     */
    public $prefix_code = [];
    /**
     * used prefix variables by current compiled tag
     *
     * @var array
     */
    public $used_prefix_variables = [];
    /**
     * Prefix code  stack
     *
     * @var array
     */
    public $prefix_code_stack = [];
    /**
     * Tag has compiled code
     *
     * @var bool
     */
    public $has_code = false;
    /**
     * A variable string was compiled
     *
     * @var bool
     */
    public $has_variable_string = false;
    /**
     * Stack for {setfilter} {/setfilter}
     *
     * @var array
     */
    public $variable_filter_stack = [];
    /**
     * variable filters for {setfilter} {/setfilter}
     *
     * @var array
     */
    public $variable_filters = [];
    /**
     * Nesting count of looping tags like {foreach}, {for}, {section}, {while}
     *
     * @var int
     */
    public $loop_nesting = 0;
    /**
     * Strip preg pattern
     *
     * @var string
     */
    public $strip_reg_ex = '![\t ]*[\r\n]+[\t ]*!';
    /**
     * plugin search order
     *
     * @var array
     */
    public $plugin_search_order = ['function', 'block', 'compiler', 'class'];
    /**
     * General storage area for tag compiler plugins
     *
     * @var array
     */
    public $_cache = [];
    /**
     * Lexer preg pattern for left delimiter
     *
     * @var string
     */
    private $ldel_preg = '[{]';
    /**
     * Lexer preg pattern for right delimiter
     *
     * @var string
     */
    private $rdel_preg = '[}]';
    /**
     * Length of right delimiter
     *
     * @var int
     */
    private $rdel_length = 0;
    /**
     * Length of left delimiter
     *
     * @var int
     */
    private $ldel_length = 0;
    /**
     * Lexer preg pattern for user literals
     *
     * @var string
     */
    private $literal_preg = '';
    /**
     * Initialize compiler
     *
     * @param Smarty $smarty global instance
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
        $this->nocache_hash = str_replace(['.', ','], '_', uniqid(mt_rand(), true));
    }
    /**
     * Method to compile a Smarty template
     *
     * @param Smarty_Internal_Template                  $template template object to compile
     * @param bool                                      $nocache  true is shall be compiled in nocache mode
     * @param null|Smarty_Internal_TemplateCompilerBase $parent_compiler
     *
     * @return bool true if compiling succeeded, false if it failed
     * @throws \Exception
     */
    public function compile_template(Smarty_Internal_Template $template, $nocache = null, Smarty_internal_template_Compiler_Base $parent_compiler = null)
    {
        // get code frame of compiled template
        $_compiled_code = $template->smarty->ext->_code_frame->create($template, $this->compile_template_source($template, $nocache, $parent_compiler), $this->post_filter($this->block_or_function_code) . join('', $this->merged_sub_templates_code), false, $this);
        return $_compiled_code;
    }
    /**
     * Compile template source and run optional post filter
     *
     * @param \Smarty_Internal_Template             $template
     * @param null|bool                             $nocache flag if template must be compiled in nocache mode
     * @param \Smarty_Internal_TemplateCompilerBase $parent_compiler
     *
     * @return string
     * @throws \Exception
     */
    public function compile_template_source(Smarty_Internal_Template $template, $nocache = null, Smarty_internal_template_Compiler_Base $parent_compiler = null)
    {
        try {
            // save template object in compiler class
            $this->template = $template;
            if ($this->smarty->debugging) {
                if (!isset($this->smarty->_debug)) {
                    $this->smarty->_debug = new Smarty_Internal_Debug();
                }
                $this->smarty->_debug->start_compile($this->template);
            }
            $this->parent_compiler = $parent_compiler ? $parent_compiler : $this;
            $nocache = isset($nocache) ? $nocache : false;
            if (empty($template->compiled->nocache_hash)) {
                $template->compiled->nocache_hash = $this->nocache_hash;
            } else {
                $this->nocache_hash = $template->compiled->nocache_hash;
            }
            $this->caching = $template->caching;
            // flag for nocache sections
            $this->nocache = $nocache;
            $this->tag_nocache = false;
            // reset has nocache code flag
            $this->template->compiled->has_nocache_code = false;
            $this->has_variable_string = false;
            $this->prefix_code = [];
            // add file dependency
            if ($this->smarty->merge_compiled_includes || $this->template->source->handler->check_timestamps()) {
                $this->parent_compiler->template->compiled->file_dependency[$this->template->source->uid] = [$this->template->source->filepath, $this->template->source->get_time_stamp(), $this->template->source->type];
            }
            $this->smarty->_current_file = $this->template->source->filepath;
            // get template source
            if (!empty($this->template->source->components)) {
                // we have array of inheritance templates by extends: resource
                // generate corresponding source code sequence
                $_content = Smarty_Internal_Compile_Extends::extends_source_array_code($this->template);
            } else {
                // get template source
                $_content = $this->template->source->get_content();
            }
            $_compiled_code = $this->post_filter($this->do_compile($this->pre_filter($_content), true));
            if (!empty($this->required_plugins['compiled']) || !empty($this->required_plugins['nocache'])) {
                $_compiled_code = '<?php ' . $this->compile_required_plugins() . "?>\n" . $_compiled_code;
            }
        } catch (Exception $e) {
            if ($this->smarty->debugging) {
                $this->smarty->_debug->end_compile($this->template);
            }
            $this->_tag_stack = [];
            // free memory
            $this->parent_compiler = null;
            $this->template = null;
            $this->parser = null;
            throw $e;
        }
        if ($this->smarty->debugging) {
            $this->smarty->_debug->end_compile($this->template);
        }
        $this->parent_compiler = null;
        $this->parser = null;
        return $_compiled_code;
    }
    /**
     * Optionally process compiled code by post filter
     *
     * @param string $code compiled code
     *
     * @return string
     * @throws \SmartyException
     */
    public function post_filter($code)
    {
        // run post filter if on code
        if (!empty($code) && (isset($this->smarty->autoload_filters['post']) || isset($this->smarty->registered_filters['post']))) {
            return $this->smarty->ext->_filter_handler->run_filter('post', $code, $this->template);
        } else {
            return $code;
        }
    }
    /**
     * Run optional prefilter
     *
     * @param string $_content template source
     *
     * @return string
     * @throws \SmartyException
     */
    public function pre_filter($_content)
    {
        // run pre filter if required
        if ($_content !== '' && (isset($this->smarty->autoload_filters['pre']) || isset($this->smarty->registered_filters['pre']))) {
            return $this->smarty->ext->_filter_handler->run_filter('pre', $_content, $this->template);
        } else {
            return $_content;
        }
    }
    /**
     * Compile Tag
     * This is a call back from the lexer/parser
     *
     * Save current prefix code
     * Compile tag
     * Merge tag prefix code with saved one
     * (required nested tags in attributes)
     *
     * @param string $tag       tag name
     * @param array  $args      array with tag attributes
     * @param array  $parameter array with compilation parameter
     *
     * @throws SmartyCompilerException
     * @throws SmartyException
     * @return string compiled code
     */
    public function compile_tag($tag, $args, $parameter = [])
    {
        $this->prefix_code_stack[] = $this->prefix_code;
        $this->prefix_code = [];
        $result = $this->compile_tag2($tag, $args, $parameter);
        $this->prefix_code = array_merge($this->prefix_code, array_pop($this->prefix_code_stack));
        return $result;
    }
    /**
     * compile variable
     *
     * @param string $variable
     *
     * @return string
     */
    public function compile_variable($variable)
    {
        if (!strpos($variable, '(')) {
            // not a variable variable
            $var = trim($variable, '\'');
            $this->tag_nocache = $this->tag_nocache | $this->template->ext->get_template_vars->_get_variable($this->template, $var, null, true, false)->nocache;
            // todo $this->template->compiled->properties['variables'][$var] = $this->tag_nocache | $this->nocache;
        }
        return '$_smarty_tpl->tpl_vars[' . $variable . ']->value';
    }
    /**
     * compile config variable
     *
     * @param string $variable
     *
     * @return string
     */
    public function compile_config_variable($variable)
    {
        // return '$_smarty_tpl->config_vars[' . $variable . ']';
        return '$_smarty_tpl->smarty->ext->configLoad->_getConfigVariable($_smarty_tpl, ' . $variable . ')';
    }
    /**
     * compile PHP function call
     *
     * @param string $name
     * @param array  $parameter
     *
     * @return string
     * @throws \SmartyCompilerException
     */
    public function compile_php_function_call($name, $parameter)
    {
        if (!$this->smarty->security_policy || $this->smarty->security_policy->is_trusted_php_function($name, $this)) {
            if (strcasecmp($name, 'isset') === 0 || strcasecmp($name, 'empty') === 0 || strcasecmp($name, 'array') === 0 || is_callable($name)) {
                $func_name = smarty_strtolower_ascii($name);
                if ($func_name === 'isset') {
                    if (count($parameter) === 0) {
                        $this->trigger_template_error('Illegal number of parameter in "isset()"');
                    }
                    $pa = [];
                    foreach ($parameter as $p) {
                        $pa[] = $this->syntax_matches_variable($p) ? 'isset(' . $p . ')' : '(' . $p . ' !== null )';
                    }
                    return '(' . implode(' && ', $pa) . ')';
                } elseif (in_array($func_name, ['empty', 'reset', 'current', 'end', 'prev', 'next'])) {
                    if (count($parameter) !== 1) {
                        $this->trigger_template_error("Illegal number of parameter in '{$func_name()}'");
                    }
                    if ($func_name === 'empty') {
                        return $func_name . '(' . str_replace("')->value", "',null,true,false)->value", $parameter[0]) . ')';
                    } else {
                        return $func_name . '(' . $parameter[0] . ')';
                    }
                } else {
                    return $name . '(' . implode(',', $parameter) . ')';
                }
            } else {
                $this->trigger_template_error("unknown function '{$name}'");
            }
        }
    }
    /**
     * Determines whether the passed string represents a valid (PHP) variable.
     * This is important, because `isset()` only works on variables and `empty()` can only be passed
     * a variable prior to php5.5
     * @param $string
     * @return bool
     */
    private function syntax_matches_variable($string)
    {
        static $regex_pattern = '/^\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*((->)[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*|\[.*]*\])*$/';
        return 1 === preg_match($regex_pattern, trim($string));
    }
    /**
     * This method is called from parser to process a text content section if strip is enabled
     * - remove text from inheritance child templates as they may generate output
     *
     * @param string $text
     *
     * @return string
     */
    public function process_text($text)
    {
        if (strpos($text, '<') === false) {
            return preg_replace($this->strip_reg_ex, '', $text);
        }
        $store = [];
        $_store = 0;
        // capture html elements not to be messed with
        $_offset = 0;
        if (preg_match_all('#(<script[^>]*>.*?</script[^>]*>)|(<textarea[^>]*>.*?</textarea[^>]*>)|(<pre[^>]*>.*?</pre[^>]*>)#is', $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $store[] = $match[0][0];
                $_length = strlen($match[0][0]);
                $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
                $text = substr_replace($text, $replace, $match[0][1] - $_offset, $_length);
                $_offset += $_length - strlen($replace);
                $_store++;
            }
        }
        $expressions = [
            // replace multiple spaces between tags by a single space
            '#(:SMARTY@!@|>)[\040\011]+(?=@!@SMARTY:|<)#s' => '\1 \2',
            // remove newline between tags
            '#(:SMARTY@!@|>)[\040\011]*[\n]\s*(?=@!@SMARTY:|<)#s' => '\1\2',
            // remove multiple spaces between attributes (but not in attribute values!)
            '#(([a-z0-9]\s*=\s*("[^"]*?")|(\'[^\']*?\'))|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \5',
            '#>[\040\011]+$#Ss' => '> ',
            '#>[\040\011]*[\n]\s*$#Ss' => '>',
            $this->strip_reg_ex => '',
        ];
        $text = preg_replace(array_keys($expressions), array_values($expressions), $text);
        $_offset = 0;
        if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $_length = strlen($match[0][0]);
                $replace = $store[$match[1][0]];
                $text = substr_replace($text, $replace, $match[0][1] + $_offset, $_length);
                $_offset += strlen($replace) - $_length;
                $_store++;
            }
        }
        return $text;
    }
    /**
     * lazy loads internal compile plugin for tag and calls the compile method
     * compile objects cached for reuse.
     * class name format:  Smarty_Internal_Compile_TagName
     * plugin filename format: Smarty_Internal_TagName.php
     *
     * @param string $tag    tag name
     * @param array  $args   list of tag attributes
     * @param mixed  $param1 optional parameter
     * @param mixed  $param2 optional parameter
     * @param mixed  $param3 optional parameter
     *
     * @return bool|string compiled code or false
     * @throws \SmartyCompilerException
     */
    public function call_tag_compiler($tag, $args, $param1 = null, $param2 = null, $param3 = null)
    {
        /* @var Smarty_Internal_CompileBase $tagCompiler */
        $tag_compiler = $this->get_tag_compiler($tag);
        // compile this tag
        return $tag_compiler === false ? false : $tag_compiler->compile($args, $this, $param1, $param2, $param3);
    }
    /**
     * lazy loads internal compile plugin for tag compile objects cached for reuse.
     *
     * class name format:  Smarty_Internal_Compile_TagName
     * plugin filename format: Smarty_Internal_TagName.php
     *
     * @param string $tag tag name
     *
     * @return bool|\Smarty_Internal_CompileBase tag compiler object or false if not found
     */
    public function get_tag_compiler($tag)
    {
        // re-use object if already exists
        if (!isset(self::$_tag_objects[$tag])) {
            // lazy load internal compiler plugin
            $_tag = explode('_', $tag);
            $_tag = array_map('smarty_ucfirst_ascii', $_tag);
            $class_name = 'Smarty_Internal_Compile_' . implode('_', $_tag);
            if (class_exists($class_name) && (!isset($this->smarty->security_policy) || $this->smarty->security_policy->is_trusted_tag($tag, $this))) {
                self::$_tag_objects[$tag] = new $class_name();
            } else {
                self::$_tag_objects[$tag] = false;
            }
        }
        return self::$_tag_objects[$tag];
    }
    /**
     * Check for plugins and return function name
     *
     * @param        $plugin_name
     * @param string $plugin_type type of plugin
     *
     * @return string call name of function
     * @throws \SmartyException
     */
    public function get_plugin($plugin_name, $plugin_type)
    {
        $function = null;
        if ($this->caching && ($this->nocache || $this->tag_nocache)) {
            if (isset($this->required_plugins['nocache'][$plugin_name][$plugin_type])) {
                $function = $this->required_plugins['nocache'][$plugin_name][$plugin_type]['function'];
            } elseif (isset($this->required_plugins['compiled'][$plugin_name][$plugin_type])) {
                $this->required_plugins['nocache'][$plugin_name][$plugin_type] = $this->required_plugins['compiled'][$plugin_name][$plugin_type];
                $function = $this->required_plugins['nocache'][$plugin_name][$plugin_type]['function'];
            }
        } else if (isset($this->required_plugins['compiled'][$plugin_name][$plugin_type])) {
            $function = $this->required_plugins['compiled'][$plugin_name][$plugin_type]['function'];
        } elseif (isset($this->required_plugins['nocache'][$plugin_name][$plugin_type])) {
            $this->required_plugins['compiled'][$plugin_name][$plugin_type] = $this->required_plugins['nocache'][$plugin_name][$plugin_type];
            $function = $this->required_plugins['compiled'][$plugin_name][$plugin_type]['function'];
        }
        if (isset($function)) {
            if ($plugin_type === 'modifier') {
                $this->modifier_plugins[$plugin_name] = true;
            }
            return $function;
        }
        // loop through plugin dirs and find the plugin
        $function = 'smarty_' . $plugin_type . '_' . $plugin_name;
        $file = $this->smarty->load_plugin($function, false);
        if (is_string($file)) {
            if ($this->caching && ($this->nocache || $this->tag_nocache)) {
                $this->required_plugins['nocache'][$plugin_name][$plugin_type]['file'] = $file;
                $this->required_plugins['nocache'][$plugin_name][$plugin_type]['function'] = $function;
            } else {
                $this->required_plugins['compiled'][$plugin_name][$plugin_type]['file'] = $file;
                $this->required_plugins['compiled'][$plugin_name][$plugin_type]['function'] = $function;
            }
            if ($plugin_type === 'modifier') {
                $this->modifier_plugins[$plugin_name] = true;
            }
            return $function;
        }
        if (is_callable($function)) {
            // plugin function is defined in the script
            return $function;
        }
        return false;
    }
    /**
     * Check for plugins by default plugin handler
     *
     * @param string $tag         name of tag
     * @param string $plugin_type type of plugin
     *
     * @return bool true if found
     * @throws \SmartyCompilerException
     */
    public function get_plugin_from_default_handler($tag, $plugin_type)
    {
        $callback = null;
        $script = null;
        $cacheable = true;
        $result = call_user_func_array($this->smarty->default_plugin_handler_func, [$tag, $plugin_type, $this->template, &$callback, &$script, &$cacheable]);
        if ($result) {
            $this->tag_nocache = $this->tag_nocache || !$cacheable;
            if ($script !== null) {
                if (is_file($script)) {
                    if ($this->caching && ($this->nocache || $this->tag_nocache)) {
                        $this->required_plugins['nocache'][$tag][$plugin_type]['file'] = $script;
                        $this->required_plugins['nocache'][$tag][$plugin_type]['function'] = $callback;
                    } else {
                        $this->required_plugins['compiled'][$tag][$plugin_type]['file'] = $script;
                        $this->required_plugins['compiled'][$tag][$plugin_type]['function'] = $callback;
                    }
                    include_once $script;
                } else {
                    $this->trigger_template_error("Default plugin handler: Returned script file '{$script}' for '{$tag}' not found");
                }
            }
            if (is_callable($callback)) {
                $this->default_handler_plugins[$plugin_type][$tag] = [$callback, true, []];
                return true;
            } else {
                $this->trigger_template_error("Default plugin handler: Returned callback for '{$tag}' not callable");
            }
        }
        return false;
    }
    /**
     * Append code segments and remove unneeded ?> <?php transitions
     *
     * @param string $left
     * @param string $right
     *
     * @return string
     */
    public function append_code($left, $right)
    {
        if (preg_match('/\s*\?>\s?$/D', $left) && preg_match('/^<\?php\s+/', $right)) {
            $left = preg_replace('/\s*\?>\s?$/D', "\n", $left);
            $left .= preg_replace('/^<\?php\s+/', '', $right);
        } else {
            $left .= $right;
        }
        return $left;
    }
    /**
     * Inject inline code for nocache template sections
     * This method gets the content of each template element from the parser.
     * If the content is compiled code and it should be not cached the code is injected
     * into the rendered output.
     *
     * @param string  $content content of template element
     * @param boolean $is_code true if content is compiled code
     *
     * @return string  content
     */
    public function process_nocache_code($content, $is_code)
    {
        // If the template is not evaluated and we have a nocache section and or a nocache tag
        if ($is_code && !empty($content)) {
            // generate replacement code
            if ((!$this->template->source->handler->recompiled || $this->force_nocache) && $this->caching && !$this->suppress_nocache_processing && ($this->nocache || $this->tag_nocache)) {
                $this->template->compiled->has_nocache_code = true;
                $_output = addcslashes($content, '\'\\');
                $_output = str_replace('^#^', '\'', $_output);
                $_output = "<?php echo '/*%%SmartyNocache:{$this->nocache_hash}%%*/{$_output}/*/%%SmartyNocache:{$this->nocache_hash}%%*/';?>\n";
                // make sure we include modifier plugins for nocache code
                foreach ($this->modifier_plugins as $plugin_name => $dummy) {
                    if (isset($this->required_plugins['compiled'][$plugin_name]['modifier'])) {
                        $this->required_plugins['nocache'][$plugin_name]['modifier'] = $this->required_plugins['compiled'][$plugin_name]['modifier'];
                    }
                }
            } else {
                $_output = $content;
            }
        } else {
            $_output = $content;
        }
        $this->modifier_plugins = [];
        $this->suppress_nocache_processing = false;
        $this->tag_nocache = false;
        return $_output;
    }
    /**
     * Get Id
     *
     * @param string $input
     *
     * @return bool|string
     */
    public function get_id($input)
    {
        if (preg_match('~^([\'"]*)([0-9]*[a-zA-Z_]\w*)\1$~', $input, $match)) {
            return $match[2];
        }
        return false;
    }
    /**
     * Get variable name from string
     *
     * @param string $input
     *
     * @return bool|string
     */
    public function get_variable_name($input)
    {
        if (preg_match('~^[$]_smarty_tpl->tpl_vars\[[\'"]*([0-9]*[a-zA-Z_]\w*)[\'"]*\]->value$~', $input, $match)) {
            return $match[1];
        }
        return false;
    }
    /**
     * Set nocache flag in variable or create new variable
     *
     * @param string $varName
     */
    public function set_nocache_in_variable($var_name)
    {
        // create nocache var to make it know for further compiling
        if ($_var = $this->get_id($var_name)) {
            if (isset($this->template->tpl_vars[$_var])) {
                $this->template->tpl_vars[$_var] = clone $this->template->tpl_vars[$_var];
                $this->template->tpl_vars[$_var]->nocache = true;
            } else {
                $this->template->tpl_vars[$_var] = new Smarty_Variable(null, true);
            }
        }
    }
    /**
     * @param array $_attr tag attributes
     * @param array $validScopes
     *
     * @return int|string
     * @throws \SmartyCompilerException
     */
    public function convert_scope($_attr, $valid_scopes)
    {
        $_scope = 0;
        if (isset($_attr['scope'])) {
            $_scope_name = trim($_attr['scope'], '\'"');
            if (is_numeric($_scope_name) && in_array($_scope_name, $valid_scopes)) {
                $_scope = $_scope_name;
            } elseif (is_string($_scope_name)) {
                $_scope_name = trim($_scope_name, '\'"');
                $_scope = isset($valid_scopes[$_scope_name]) ? $valid_scopes[$_scope_name] : false;
            } else {
                $_scope = false;
            }
            if ($_scope === false) {
                $err = var_export($_scope_name, true);
                $this->trigger_template_error("illegal value '{$err}' for \"scope\" attribute", null, true);
            }
        }
        return $_scope;
    }
    /**
     * Generate nocache code string
     *
     * @param string $code PHP code
     *
     * @return string
     */
    public function make_nocache_code($code)
    {
        return "echo '/*%%SmartyNocache:{$this->nocache_hash}%%*/<?php " . str_replace('^#^', '\'', addcslashes($code, '\'\\')) . "?>/*/%%SmartyNocache:{$this->nocache_hash}%%*/';\n";
    }
    /**
     * display compiler error messages without dying
     * If parameter $args is empty it is a parser detected syntax error.
     * In this case the parser is called to obtain information about expected tokens.
     * If parameter $args contains a string this is used as error message
     *
     * @param string    $args    individual error message or null
     * @param string    $line    line-number
     * @param null|bool $tagline if true the line number of last tag
     *
     * @throws \SmartyCompilerException when an unexpected token is found
     */
    public function trigger_template_error($args = null, $line = null, $tagline = null)
    {
        $lex = $this->parser->lex;
        if ($tagline === true) {
            // get line number of Tag
            $line = $lex->taglineno;
        } elseif (!isset($line)) {
            // get template source line which has error
            $line = $lex->line;
        } else {
            $line = (int) $line;
        }
        if (in_array($this->template->source->type, ['eval', 'string'])) {
            $template_name = $this->template->source->type . ':' . trim(preg_replace('![\t\r\n]+!', ' ', strlen($lex->data) > 40 ? substr($lex->data, 0, 40) . '...' : $lex->data));
        } else {
            $template_name = $this->template->source->type . ':' . $this->template->source->filepath;
        }
        //        $line += $this->trace_line_offset;
        $match = preg_split("/\n/", $lex->data);
        $error_text = 'Syntax error in template "' . (empty($this->trace_filepath) ? $template_name : $this->trace_filepath) . '"  on line ' . ($line + $this->trace_line_offset) . ' "' . trim(preg_replace('![\t\r\n]+!', ' ', $match[$line - 1])) . '" ';
        if (isset($args)) {
            // individual error message
            $error_text .= $args;
        } else {
            $expect = [];
            // expected token from parser
            $error_text .= ' - Unexpected "' . $lex->value . '"';
            if (count($this->parser->yy_get_expected_tokens($this->parser->yymajor)) <= 4) {
                foreach ($this->parser->yy_get_expected_tokens($this->parser->yymajor) as $token) {
                    $exp_token = $this->parser->yy_token_name[$token];
                    if (isset($lex->smarty_token_names[$exp_token])) {
                        // token type from lexer
                        $expect[] = '"' . $lex->smarty_token_names[$exp_token] . '"';
                    } else {
                        // otherwise internal token name
                        $expect[] = $this->parser->yy_token_name[$token];
                    }
                }
                $error_text .= ', expected one of: ' . implode(' , ', $expect);
            }
        }
        if ($this->smarty->_parserdebug) {
            $this->parser->error_run_down();
            echo ob_get_clean();
            flush();
        }
        $e = new Smarty_Compiler_Exception($error_text, 0, $this->template->source->filepath, $line);
        $e->source = trim(preg_replace('![\t\r\n]+!', ' ', $match[$line - 1]));
        $e->desc = $args;
        $e->template = $this->template->source->filepath;
        throw $e;
    }
    /**
     * Return var_export() value with all white spaces removed
     *
     * @param mixed $value
     *
     * @return string
     */
    public function get_var_export($value)
    {
        return preg_replace('/\s/', '', var_export($value, true));
    }
    /**
     *  enter double quoted string
     *  - save tag stack count
     */
    public function enter_double_quote()
    {
        array_push($this->_tag_stack_count, $this->get_tag_stack_count());
    }
    /**
     * Return tag stack count
     *
     * @return int
     */
    public function get_tag_stack_count()
    {
        return count($this->_tag_stack);
    }
    /**
     * @param $lexerPreg
     *
     * @return mixed
     */
    public function replace_delimiter($lexer_preg)
    {
        return str_replace(['SMARTYldel', 'SMARTYliteral', 'SMARTYrdel', 'SMARTYautoliteral', 'SMARTYal'], [$this->ldel_preg, $this->literal_preg, $this->rdel_preg, $this->smarty->get_auto_literal() ? '{1,}' : '{9}', $this->smarty->get_auto_literal() ? '' : '\s*'], $lexer_preg);
    }
    /**
     * Build lexer regular expressions for left and right delimiter and user defined literals
     */
    public function init_delimiter_preg()
    {
        $ldel = $this->smarty->get_left_delimiter();
        $this->ldel_length = strlen($ldel);
        $this->ldel_preg = '';
        foreach (str_split($ldel, 1) as $chr) {
            $this->ldel_preg .= '[' . preg_quote($chr, '/') . ']';
        }
        $rdel = $this->smarty->get_right_delimiter();
        $this->rdel_length = strlen($rdel);
        $this->rdel_preg = '';
        foreach (str_split($rdel, 1) as $chr) {
            $this->rdel_preg .= '[' . preg_quote($chr, '/') . ']';
        }
        $literals = $this->smarty->get_literals();
        if (!empty($literals)) {
            foreach ($literals as $key => $literal) {
                $literal_preg = '';
                foreach (str_split($literal, 1) as $chr) {
                    $literal_preg .= '[' . preg_quote($chr, '/') . ']';
                }
                $literals[$key] = $literal_preg;
            }
            $this->literal_preg = '|' . implode('|', $literals);
        } else {
            $this->literal_preg = '';
        }
    }
    /**
     *  leave double quoted string
     *  - throw exception if block in string was not closed
     *
     * @throws \SmartyCompilerException
     */
    public function leave_double_quote()
    {
        if (array_pop($this->_tag_stack_count) !== $this->get_tag_stack_count()) {
            $tag = $this->get_open_block_tag();
            $this->trigger_template_error("unclosed '{{$tag}}' in doubled quoted string", null, true);
        }
    }
    /**
     * Get left delimiter preg
     *
     * @return string
     */
    public function get_ldel_preg()
    {
        return $this->ldel_preg;
    }
    /**
     * Get right delimiter preg
     *
     * @return string
     */
    public function get_rdel_preg()
    {
        return $this->rdel_preg;
    }
    /**
     * Get length of left delimiter
     *
     * @return int
     */
    public function get_ldel_length()
    {
        return $this->ldel_length;
    }
    /**
     * Get length of right delimiter
     *
     * @return int
     */
    public function get_rdel_length()
    {
        return $this->rdel_length;
    }
    /**
     * Get name of current open block tag
     *
     * @return string|boolean
     */
    public function get_open_block_tag()
    {
        $tag_count = $this->get_tag_stack_count();
        if ($tag_count) {
            return $this->_tag_stack[$tag_count - 1][0];
        } else {
            return false;
        }
    }
    /**
     * Check if $value contains variable elements
     *
     * @param mixed $value
     *
     * @return bool|int
     */
    public function is_variable($value)
    {
        if (is_string($value)) {
            return preg_match('/[$(]/', $value);
        }
        if (is_bool($value) || is_numeric($value)) {
            return false;
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if ($this->is_variable($k) || $this->is_variable($v)) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    /**
     * Get new prefix variable name
     *
     * @return string
     */
    public function get_new_prefix_variable()
    {
        ++self::$prefix_variable_number;
        return $this->get_prefix_variable();
    }
    /**
     * Get current prefix variable name
     *
     * @return string
     */
    public function get_prefix_variable()
    {
        return '$_prefixVariable' . self::$prefix_variable_number;
    }
    /**
     * append  code to prefix buffer
     *
     * @param string $code
     */
    public function append_prefix_code($code)
    {
        $this->prefix_code[] = $code;
    }
    /**
     * get prefix code string
     *
     * @return string
     */
    public function get_prefix_code()
    {
        $code = '';
        $prefix_array = array_merge($this->prefix_code, array_pop($this->prefix_code_stack));
        $this->prefix_code_stack[] = [];
        foreach ($prefix_array as $c) {
            $code = $this->append_code($code, $c);
        }
        $this->prefix_code = [];
        return $code;
    }
    /**
     * Save current required plugins
     *
     * @param bool $init if true init required plugins
     */
    public function save_required_plugins($init = false)
    {
        $this->required_plugins_stack[] = $this->required_plugins;
        if ($init) {
            $this->required_plugins = ['compiled' => [], 'nocache' => []];
        }
    }
    /**
     * Restore required plugins
     */
    public function restore_required_plugins()
    {
        $this->required_plugins = array_pop($this->required_plugins_stack);
    }
    /**
     * Compile code to call Smarty_Internal_Template::_checkPlugins()
     * for required plugins
     *
     * @return string
     */
    public function compile_required_plugins()
    {
        $code = $this->compile_check_plugins($this->required_plugins['compiled']);
        if ($this->caching && !empty($this->required_plugins['nocache'])) {
            $code .= $this->make_nocache_code($this->compile_check_plugins($this->required_plugins['nocache']));
        }
        return $code;
    }
    /**
     * Compile code to call Smarty_Internal_Template::_checkPlugins
     *   - checks if plugin is callable require otherwise
     *
     * @param $requiredPlugins
     *
     * @return string
     */
    public function compile_check_plugins($required_plugins)
    {
        if (!empty($required_plugins)) {
            $plugins = [];
            foreach ($required_plugins as $plugin) {
                foreach ($plugin as $data) {
                    $plugins[] = $data;
                }
            }
            return '$_smarty_tpl->_checkPlugins(' . $this->get_var_export($plugins) . ');' . "\n";
        } else {
            return '';
        }
    }
    /**
     * method to compile a Smarty template
     *
     * @param mixed $_content template source
     * @param bool  $isTemplateSource
     *
     * @return bool true if compiling succeeded, false if it failed
     */
    abstract protected function do_compile($_content, $is_template_source = false);
    public function c_style_comment($string)
    {
        return '/*' . str_replace('*/', '* /', $string) . '*/';
    }
    /**
     * Compile Tag
     *
     * @param string $tag       tag name
     * @param array  $args      array with tag attributes
     * @param array  $parameter array with compilation parameter
     *
     * @throws SmartyCompilerException
     * @throws SmartyException
     * @return string compiled code
     */
    private function compile_tag2($tag, $args, $parameter)
    {
        $plugin_type = '';
        // $args contains the attributes parsed and compiled by the lexer/parser
        // assume that tag does compile into code, but creates no HTML output
        $this->has_code = true;
        // log tag/attributes
        if (isset($this->smarty->_cache['get_used_tags'])) {
            $this->template->_cache['used_tags'][] = [$tag, $args];
        }
        // check nocache option flag
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                if ($arg === "'nocache'" || $arg === 'nocache') {
                    $this->tag_nocache = true;
                }
            } else {
                foreach ($arg as $k => $v) {
                    if (($k === "'nocache'" || $k === 'nocache') && trim($v, "'\" ") === 'true') {
                        $this->tag_nocache = true;
                    }
                }
            }
        }
        // compile the smarty tag (required compile classes to compile the tag are auto loaded)
        if (($_output = $this->call_tag_compiler($tag, $args, $parameter)) === false) {
            if (isset($this->parent_compiler->tpl_function[$tag]) || isset($this->template->smarty->ext->_tpl_function) && $this->template->smarty->ext->_tpl_function->get_tpl_function($this->template, $tag) !== false) {
                // template defined by {template} tag
                $args['_attr']['name'] = "'{$tag}'";
                $_output = $this->call_tag_compiler('call', $args, $parameter);
            }
        }
        if ($_output !== false) {
            if ($_output !== true) {
                // did we get compiled code
                if ($this->has_code) {
                    // return compiled code
                    return $_output;
                }
            }
            // tag did not produce compiled code
            return null;
        } else {
            // map_named attributes
            if (isset($args['_attr'])) {
                foreach ($args['_attr'] as $key => $attribute) {
                    if (is_array($attribute)) {
                        $args = array_merge($args, $attribute);
                    }
                }
            }
            // not an internal compiler tag
            if (strlen($tag) < 6 || substr($tag, -5) !== 'close') {
                // check if tag is a registered object
                if (isset($this->smarty->registered_objects[$tag]) && isset($parameter['object_method'])) {
                    $method = $parameter['object_method'];
                    if (!in_array($method, $this->smarty->registered_objects[$tag][3]) && (empty($this->smarty->registered_objects[$tag][1]) || in_array($method, $this->smarty->registered_objects[$tag][1]))) {
                        return $this->call_tag_compiler('private_object_function', $args, $parameter, $tag, $method);
                    } elseif (in_array($method, $this->smarty->registered_objects[$tag][3])) {
                        return $this->call_tag_compiler('private_object_block_function', $args, $parameter, $tag, $method);
                    } else {
                        // throw exception
                        $this->trigger_template_error('not allowed method "' . $method . '" in registered object "' . $tag . '"', null, true);
                    }
                }
                // check if tag is registered
                foreach ([Smarty::PLUGIN_COMPILER, Smarty::PLUGIN_FUNCTION, Smarty::PLUGIN_BLOCK] as $plugin_type) {
                    if (isset($this->smarty->registered_plugins[$plugin_type][$tag])) {
                        // if compiler function plugin call it now
                        if ($plugin_type === Smarty::PLUGIN_COMPILER) {
                            $new_args = [];
                            foreach ($args as $key => $mixed) {
                                if (is_array($mixed)) {
                                    $new_args = array_merge($new_args, $mixed);
                                } else {
                                    $new_args[$key] = $mixed;
                                }
                            }
                            if (!$this->smarty->registered_plugins[$plugin_type][$tag][1]) {
                                $this->tag_nocache = true;
                            }
                            return call_user_func_array($this->smarty->registered_plugins[$plugin_type][$tag][0], [$new_args, $this]);
                        }
                        // compile registered function or block function
                        if ($plugin_type === Smarty::PLUGIN_FUNCTION || $plugin_type === Smarty::PLUGIN_BLOCK) {
                            return $this->call_tag_compiler('private_registered_' . $plugin_type, $args, $parameter, $tag);
                        }
                    }
                }
                // check plugins from plugins folder
                foreach ($this->plugin_search_order as $plugin_type) {
                    if ($plugin_type === Smarty::PLUGIN_COMPILER && $this->smarty->load_plugin('smarty_compiler_' . $tag) && (!isset($this->smarty->security_policy) || $this->smarty->security_policy->is_trusted_tag($tag, $this))) {
                        $plugin = 'smarty_compiler_' . $tag;
                        if (is_callable($plugin)) {
                            // convert arguments format for old compiler plugins
                            $new_args = [];
                            foreach ($args as $key => $mixed) {
                                if (is_array($mixed)) {
                                    $new_args = array_merge($new_args, $mixed);
                                } else {
                                    $new_args[$key] = $mixed;
                                }
                            }
                            return $plugin($new_args, $this->smarty);
                        }
                        if (class_exists($plugin, false)) {
                            $plugin_object = new $plugin();
                            if (method_exists($plugin_object, 'compile')) {
                                return $plugin_object->compile($args, $this);
                            }
                        }
                        throw new Smarty_Exception("Plugin '{$tag}' not callable");
                    } else if ($function = $this->get_plugin($tag, $plugin_type)) {
                        if (!isset($this->smarty->security_policy) || $this->smarty->security_policy->is_trusted_tag($tag, $this)) {
                            return $this->call_tag_compiler('private_' . $plugin_type . '_plugin', $args, $parameter, $tag, $function);
                        }
                    }
                }
                if (is_callable($this->smarty->default_plugin_handler_func)) {
                    $found = false;
                    // look for already resolved tags
                    foreach ($this->plugin_search_order as $plugin_type) {
                        if (isset($this->default_handler_plugins[$plugin_type][$tag])) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // call default handler
                        foreach ($this->plugin_search_order as $plugin_type) {
                            if ($this->get_plugin_from_default_handler($tag, $plugin_type)) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if ($found) {
                        // if compiler function plugin call it now
                        if ($plugin_type === Smarty::PLUGIN_COMPILER) {
                            $new_args = [];
                            foreach ($args as $key => $mixed) {
                                if (is_array($mixed)) {
                                    $new_args = array_merge($new_args, $mixed);
                                } else {
                                    $new_args[$key] = $mixed;
                                }
                            }
                            return call_user_func_array($this->default_handler_plugins[$plugin_type][$tag][0], [$new_args, $this]);
                        } else {
                            return $this->call_tag_compiler('private_registered_' . $plugin_type, $args, $parameter, $tag);
                        }
                    }
                }
            } else {
                // compile closing tag of block function
                $base_tag = substr($tag, 0, -5);
                // check if closing tag is a registered object
                if (isset($this->smarty->registered_objects[$base_tag]) && isset($parameter['object_method'])) {
                    $method = $parameter['object_method'];
                    if (in_array($method, $this->smarty->registered_objects[$base_tag][3])) {
                        return $this->call_tag_compiler('private_object_block_function', $args, $parameter, $tag, $method);
                    } else {
                        // throw exception
                        $this->trigger_template_error('not allowed closing tag method "' . $method . '" in registered object "' . $base_tag . '"', null, true);
                    }
                }
                // registered block tag ?
                if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_BLOCK][$base_tag]) || isset($this->default_handler_plugins[Smarty::PLUGIN_BLOCK][$base_tag])) {
                    return $this->call_tag_compiler('private_registered_block', $args, $parameter, $tag);
                }
                // registered function tag ?
                if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION][$tag])) {
                    return $this->call_tag_compiler('private_registered_function', $args, $parameter, $tag);
                }
                // block plugin?
                if ($function = $this->get_plugin($base_tag, Smarty::PLUGIN_BLOCK)) {
                    return $this->call_tag_compiler('private_block_plugin', $args, $parameter, $tag, $function);
                }
                // function plugin?
                if ($function = $this->get_plugin($tag, Smarty::PLUGIN_FUNCTION)) {
                    if (!isset($this->smarty->security_policy) || $this->smarty->security_policy->is_trusted_tag($tag, $this)) {
                        return $this->call_tag_compiler('private_function_plugin', $args, $parameter, $tag, $function);
                    }
                }
                // registered compiler plugin ?
                if (isset($this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag])) {
                    // if compiler function plugin call it now
                    $args = [];
                    if (!$this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag][1]) {
                        $this->tag_nocache = true;
                    }
                    return call_user_func_array($this->smarty->registered_plugins[Smarty::PLUGIN_COMPILER][$tag][0], [$args, $this]);
                }
                if ($this->smarty->load_plugin('smarty_compiler_' . $tag)) {
                    $plugin = 'smarty_compiler_' . $tag;
                    if (is_callable($plugin)) {
                        return $plugin($args, $this->smarty);
                    }
                    if (class_exists($plugin, false)) {
                        $plugin_object = new $plugin();
                        if (method_exists($plugin_object, 'compile')) {
                            return $plugin_object->compile($args, $this);
                        }
                    }
                    throw new Smarty_Exception("Plugin '{$tag}' not callable");
                }
            }
            $this->trigger_template_error("unknown tag '{$tag}'", null, true);
        }
    }
}