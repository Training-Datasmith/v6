<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Templateparser Parse Tree
 * These are classes to build parse tree  in the template parser
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Thue Kristensen
 * @author     Uwe Tews
 */
/**
 * Raw chars as part of a double quoted string.
 *
 * @package    Smarty
 * @subpackage Compiler
 * @ignore
 */
class Smarty_internal_parse_Tree_dq_Content extends Smarty_internal_parse_Tree
{
    /**
     * Create parse tree buffer with string content
     *
     * @param string $data string section
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * Return content as double quoted string
     *
     * @param \Smarty_Internal_Templateparser $parser
     *
     * @return string doubled quoted string
     */
    public function to_smarty_php(Smarty_Internal_Templateparser $parser)
    {
        return '"' . $this->data . '"';
    }
}