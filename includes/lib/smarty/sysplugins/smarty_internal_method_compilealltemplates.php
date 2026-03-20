<?php

declare (strict_types=1);
/**
 * Smarty Method CompileAllTemplates
 *
 * Smarty::compileAllTemplates() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_compile_All_Templates
{
    /**
     * Valid for Smarty object
     *
     * @var int
     */
    public $obj_map = 1;
    /**
     * Compile all template files
     *
     * @api Smarty::compileAllTemplates()
     *
     * @param \Smarty $smarty        passed smarty object
     * @param string  $extension     file extension
     * @param bool    $force_compile force all to recompile
     * @param int     $time_limit
     * @param int     $max_errors
     *
     * @return integer number of template files recompiled
     */
    public function compile_all_templates(Smarty $smarty, $extension = '.tpl', $force_compile = false, $time_limit = 0, $max_errors = null)
    {
        return $this->compile_all($smarty, $extension, $force_compile, $time_limit, $max_errors);
    }
    /**
     * Compile all template or config files
     *
     * @param \Smarty $smarty
     * @param string  $extension     template file name extension
     * @param bool    $force_compile force all to recompile
     * @param int     $time_limit    set maximum execution time
     * @param int     $max_errors    set maximum allowed errors
     * @param bool    $isConfig      flag true if called for config files
     *
     * @return int number of template files compiled
     */
    protected function compile_all(Smarty $smarty, $extension, $force_compile, $time_limit, $max_errors, $is_config = false)
    {
        // switch off time limit
        if (function_exists('set_time_limit')) {
            @set_time_limit($time_limit);
        }
        $_count = 0;
        $_error_count = 0;
        $source_dir = $is_config ? $smarty->get_config_dir() : $smarty->get_template_dir();
        // loop over array of source directories
        foreach ($source_dir as $_dir) {
            $_dir_1 = new Recursive_Directory_Iterator($_dir, defined('FilesystemIterator::FOLLOW_SYMLINKS') ? Filesystem_Iterator::FOLLOW_SYMLINKS : 0);
            $_dir_2 = new Recursive_Iterator_Iterator($_dir_1);
            foreach ($_dir_2 as $_fileinfo) {
                $_file = $_fileinfo->get_filename();
                if (substr(basename($_fileinfo->get_pathname()), 0, 1) === '.' || strpos($_file, '.svn') !== false) {
                    continue;
                }
                if (substr_compare($_file, $extension, -strlen($extension)) !== 0) {
                    continue;
                }
                if ($_fileinfo->get_path() !== substr($_dir, 0, -1)) {
                    $_file = substr($_fileinfo->get_path(), strlen($_dir)) . DIRECTORY_SEPARATOR . $_file;
                }
                echo "\n<br>", $_dir, '---', $_file;
                flush();
                $_start_time = microtime(true);
                $_smarty = clone $smarty;
                $_smarty->_cache = [];
                $_smarty->ext = new Smarty_Internal_Extension_Handler();
                $_smarty->ext->obj_type = $_smarty->_obj_type;
                $_smarty->force_compile = $force_compile;
                try {
                    /* @var Smarty_Internal_Template $_tpl */
                    $_tpl = new $smarty->template_class($_file, $_smarty);
                    $_tpl->caching = Smarty::CACHING_OFF;
                    $_tpl->source = $is_config ? Smarty_Template_Config::load($_tpl) : Smarty_Template_Source::load($_tpl);
                    if ($_tpl->must_compile()) {
                        $_tpl->compile_template_source();
                        $_count++;
                        echo ' compiled in  ', microtime(true) - $_start_time, ' seconds';
                        flush();
                    } else {
                        echo ' is up to date';
                        flush();
                    }
                } catch (Exception $e) {
                    echo "\n<br>        ------>Error: ", $e->get_message(), "<br><br>\n";
                    $_error_count++;
                }
                // free memory
                unset($_tpl);
                $_smarty->_clear_template_cache();
                if ($max_errors !== null && $_error_count === $max_errors) {
                    echo "\n<br><br>too many errors\n";
                    exit(1);
                }
            }
        }
        echo "\n<br>";
        return $_count;
    }
}