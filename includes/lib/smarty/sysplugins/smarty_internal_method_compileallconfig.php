<?php

declare (strict_types=1);
/**
 * Smarty Method CompileAllConfig
 *
 * Smarty::compileAllConfig() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_compile_All_Config extends Smarty_internal_method_compile_All_Templates
{
    /**
     * Compile all config files
     *
     * @api Smarty::compileAllConfig()
     *
     * @param \Smarty $smarty        passed smarty object
     * @param string  $extension     file extension
     * @param bool    $force_compile force all to recompile
     * @param int     $time_limit
     * @param int     $max_errors
     *
     * @return int number of template files recompiled
     */
    public function compile_all_config(Smarty $smarty, $extension = '.conf', $force_compile = false, $time_limit = 0, $max_errors = null)
    {
        return $this->compile_all($smarty, $extension, $force_compile, $time_limit, $max_errors, true);
    }
}