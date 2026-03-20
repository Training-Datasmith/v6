<?php

declare (strict_types=1);
/**
 * Smarty Method GetStreamVariable
 *
 * Smarty::getStreamVariable() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_internal_method_get_Stream_Variable
{
    /**
     * Valid for all objects
     *
     * @var int
     */
    public $obj_map = 7;
    /**
     * gets  a stream variable
     *
     * @api Smarty::getStreamVariable()
     *
     * @param \Smarty_Internal_Data|\Smarty_Internal_Template|\Smarty $data
     * @param string                                                  $variable the stream of the variable
     *
     * @return mixed
     * @throws \SmartyException
     */
    public function get_stream_variable(Smarty_Internal_Data $data, $variable)
    {
        $_result = '';
        $fp = fopen($variable, 'r+');
        if ($fp) {
            while (!feof($fp) && ($current_line = fgets($fp)) !== false) {
                $_result .= $current_line;
            }
            fclose($fp);
            return $_result;
        }
        $smarty = isset($data->smarty) ? $data->smarty : $data;
        if ($smarty->error_unassigned) {
            throw new Smarty_Exception('Undefined stream variable "' . $variable . '"');
        } else {
            return null;
        }
    }
}