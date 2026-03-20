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
 */
/**
 * A complete smarty tag.
 *
 * @package    Smarty
 * @subpackage Compiler
 * @ignore
 */
class Smarty_internal_parse_Tree_tag extends Smarty_internal_parse_Tree
{
    /**
     * Saved block nesting level
     *
     * @var int
     */
    public $saved_block_nesting;
    /**
     * Create parse tree buffer for Smarty tag
     *
     * @param \Smarty_Internal_Templateparser $parser parser object
     * @param string                          $data   content
     */
    public function __construct(Smarty_Internal_Templateparser $parser, $data)
    {
        $this->data = $data;
        $this->saved_block_nesting = $parser->block_nesting_level;
    }
    /**
     * Return buffer content
     *
     * @param \Smarty_Internal_Templateparser $parser
     *
     * @return string content
     */
    public function to_smarty_php(Smarty_Internal_Templateparser $parser)
    {
        return $this->data;
    }
    /**
     * Return complied code that loads the evaluated output of buffer content into a temporary variable
     *
     * @param \Smarty_Internal_Templateparser $parser
     *
     * @return string template code
     */
    public function assign_to_var(Smarty_Internal_Templateparser $parser)
    {
        $var = $parser->compiler->get_new_prefix_variable();
        $tmp = $parser->compiler->append_code('<?php ob_start();?>', $this->data);
        $tmp = $parser->compiler->append_code($tmp, "<?php {$var}=ob_get_clean();?>");
        $parser->compiler->prefix_code[] = sprintf('%s', $tmp);
        return $var;
    }
}