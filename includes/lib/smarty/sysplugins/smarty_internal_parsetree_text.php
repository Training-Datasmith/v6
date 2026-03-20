<?php

declare (strict_types=1);
/**
 * Smarty Internal Plugin Templateparser Parse Tree
 * These are classes to build parse tree in the template parser
 *
 * @package    Smarty
 * @subpackage Compiler
 * @author     Thue Kristensen
 * @author     Uwe Tews
 *             *
 *             template text
 * @package    Smarty
 * @subpackage Compiler
 * @ignore
 */
class Smarty_internal_parse_Tree_text extends Smarty_internal_parse_Tree
{
    /**
     * Wether this section should be stripped on output to smarty php
     * @var bool
     */
    private $to_be_stripped = false;
    /**
     * Create template text buffer
     *
     * @param string $data text
     * @param bool $toBeStripped wether this section should be stripped on output to smarty php
     */
    public function __construct($data, $to_be_stripped = false)
    {
        $this->data = $data;
        $this->to_be_stripped = $to_be_stripped;
    }
    /**
     * Wether this section should be stripped on output to smarty php
     * @return bool
     */
    public function is_to_be_stripped()
    {
        return $this->to_be_stripped;
    }
    /**
     * Return buffer content
     *
     * @param \Smarty_Internal_Templateparser $parser
     *
     * @return string text
     */
    public function to_smarty_php(Smarty_Internal_Templateparser $parser)
    {
        return $this->data;
    }
}