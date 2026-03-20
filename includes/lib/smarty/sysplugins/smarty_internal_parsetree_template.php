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
 * Template element
 *
 * @package    Smarty
 * @subpackage Compiler
 * @ignore
 */
class Smarty_internal_parse_Tree_template extends Smarty_internal_parse_Tree
{
    /**
     * Array of template elements
     *
     * @var array
     */
    public $subtrees = [];
    /**
     * Create root of parse tree for template elements
     */
    public function __construct()
    {
    }
    /**
     * Append buffer to subtree
     *
     * @param \Smarty_Internal_Templateparser $parser
     * @param Smarty_Internal_ParseTree       $subtree
     */
    public function append_subtree(Smarty_Internal_Templateparser $parser, Smarty_internal_parse_Tree $subtree)
    {
        if (!empty($subtree->subtrees)) {
            $this->subtrees = array_merge($this->subtrees, $subtree->subtrees);
        } else if ($subtree->data !== '') {
            $this->subtrees[] = $subtree;
        }
    }
    /**
     * Append array to subtree
     *
     * @param \Smarty_Internal_Templateparser $parser
     * @param \Smarty_Internal_ParseTree[]    $array
     */
    public function append_array(Smarty_Internal_Templateparser $parser, $array = [])
    {
        if (!empty($array)) {
            $this->subtrees = array_merge($this->subtrees, (array) $array);
        }
    }
    /**
     * Prepend array to subtree
     *
     * @param \Smarty_Internal_Templateparser $parser
     * @param \Smarty_Internal_ParseTree[]    $array
     */
    public function prepend_array(Smarty_Internal_Templateparser $parser, $array = [])
    {
        if (!empty($array)) {
            $this->subtrees = array_merge((array) $array, $this->subtrees);
        }
    }
    /**
     * Sanitize and merge subtree buffers together
     *
     * @param \Smarty_Internal_Templateparser $parser
     *
     * @return string template code content
     */
    public function to_smarty_php(Smarty_Internal_Templateparser $parser)
    {
        $code = '';
        foreach ($this->get_chunked_subtrees() as $chunk) {
            $text = '';
            switch ($chunk['mode']) {
                case 'textstripped':
                    foreach ($chunk['subtrees'] as $subtree) {
                        $text .= $subtree->to_smarty_php($parser);
                    }
                    $code .= preg_replace('/((<%)|(%>)|(<\?php)|(<\?)|(\?>)|(<\/?script))/', "<?php echo '\$1'; ?>\n", $parser->compiler->process_text($text));
                    break;
                case 'text':
                    foreach ($chunk['subtrees'] as $subtree) {
                        $text .= $subtree->to_smarty_php($parser);
                    }
                    $code .= preg_replace('/((<%)|(%>)|(<\?php)|(<\?)|(\?>)|(<\/?script))/', "<?php echo '\$1'; ?>\n", $text);
                    break;
                case 'tag':
                    foreach ($chunk['subtrees'] as $subtree) {
                        $text = $parser->compiler->append_code($text, $subtree->to_smarty_php($parser));
                    }
                    $code .= $text;
                    break;
                default:
                    foreach ($chunk['subtrees'] as $subtree) {
                        $text = $subtree->to_smarty_php($parser);
                    }
                    $code .= $text;
            }
        }
        return $code;
    }
    private function get_chunked_subtrees()
    {
        $chunks = [];
        $current_mode = null;
        $current_chunk = [];
        for ($key = 0, $cnt = count($this->subtrees); $key < $cnt; $key++) {
            if ($this->subtrees[$key]->data === '' && in_array($current_mode, ['textstripped', 'text', 'tag'])) {
                continue;
            }
            if ($this->subtrees[$key] instanceof Smarty_internal_parse_Tree_text && $this->subtrees[$key]->is_to_be_stripped()) {
                $new_mode = 'textstripped';
            } elseif ($this->subtrees[$key] instanceof Smarty_internal_parse_Tree_text) {
                $new_mode = 'text';
            } elseif ($this->subtrees[$key] instanceof Smarty_internal_parse_Tree_tag) {
                $new_mode = 'tag';
            } else {
                $new_mode = 'other';
            }
            if ($new_mode == $current_mode) {
                $current_chunk[] = $this->subtrees[$key];
            } else {
                $chunks[] = ['mode' => $current_mode, 'subtrees' => $current_chunk];
                $current_mode = $new_mode;
                $current_chunk = [$this->subtrees[$key]];
            }
        }
        if ($current_mode && $current_chunk) {
            $chunks[] = ['mode' => $current_mode, 'subtrees' => $current_chunk];
        }
        return $chunks;
    }
}