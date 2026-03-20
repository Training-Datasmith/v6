<?php

declare (strict_types=1);
/**
 * JSMin.php - modified PHP implementation of Douglas Crockford's JSMin.
 *
 * <code>
 * $minifiedJs = JSMin::minify($js);
 * </code>
 *
 * This is a modified port of jsmin.c. Improvements:
 *
 * Does not choke on some regexp literals containing quote characters. E.g. /'/
 *
 * Spaces are preserved after some add/sub operators, so they are not mistakenly
 * converted to post-inc/dec. E.g. a + ++b -> a+ ++b
 *
 * Preserves multi-line comments that begin with /*!
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com> (PHP port)
 * @author Steve Clay <steve@mrclay.org> (modifications + cleanup)
 * @author Andrea Giammarchi <http://www.3site.eu> (spaceBeforeRegExp)
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/jsmin-php/
 */
class Js_Min
{
    public const ORD_LF = 10;
    public const ORD_SPACE = 32;
    public const ACTION_KEEP_A = 1;
    public const ACTION_DELETE_A = 2;
    public const ACTION_DELETE_A_B = 3;
    protected $a = "\n";
    protected $b = '';
    protected $input = '';
    protected $input_index = 0;
    protected $input_length = 0;
    protected $look_ahead = null;
    protected $output = '';
    protected $last_byte_out = '';
    protected $kept_comment = '';
    /**
     * Minify Javascript.
     *
     * @param string $js Javascript to be minified
     *
     * @return string
     */
    public static function minify($js)
    {
        $jsmin = new Js_Min($js);
        return $jsmin->min();
    }
    /**
     * @param string $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }
    /**
     * Perform minification, return result
     *
     * @return string
     */
    public function min()
    {
        if ($this->output !== '') {
            // min already run
            return $this->output;
        }
        $mb_int_enc = null;
        if (function_exists('mb_strlen') && (int) ini_get('mbstring.func_overload') & 2) {
            $mb_int_enc = mb_internal_encoding();
            mb_internal_encoding('8bit');
        }
        $this->input = str_replace("\r\n", "\n", $this->input);
        $this->input_length = strlen($this->input);
        $this->action(self::ACTION_DELETE_A_B);
        while ($this->a !== null) {
            // determine next command
            $command = self::ACTION_KEEP_A;
            // default
            if ($this->a === ' ') {
                if (($this->last_byte_out === '+' || $this->last_byte_out === '-') && $this->b === $this->last_byte_out) {
                    // Don't delete this space. If we do, the addition/subtraction
                    // could be parsed as a post-increment
                } elseif (!$this->is_alpha_num($this->b)) {
                    $command = self::ACTION_DELETE_A;
                }
            } elseif ($this->a === "\n") {
                if ($this->b === ' ') {
                    $command = self::ACTION_DELETE_A_B;
                    // in case of mbstring.func_overload & 2, must check for null b,
                    // otherwise mb_strpos will give WARNING
                } elseif ($this->b === null || false === strpos('{[(+-!~', $this->b) && !$this->is_alpha_num($this->b)) {
                    $command = self::ACTION_DELETE_A;
                }
            } elseif (!$this->is_alpha_num($this->a)) {
                if ($this->b === ' ' || $this->b === "\n" && false === strpos('}])+-"\'', $this->a)) {
                    $command = self::ACTION_DELETE_A_B;
                }
            }
            $this->action($command);
        }
        $this->output = trim($this->output);
        if ($mb_int_enc !== null) {
            mb_internal_encoding($mb_int_enc);
        }
        return $this->output;
    }
    /**
     * ACTION_KEEP_A = Output A. Copy B to A. Get the next B.
     * ACTION_DELETE_A = Copy B to A. Get the next B.
     * ACTION_DELETE_A_B = Get the next B.
     *
     * @param int $command
     * @throws JSMin_UnterminatedRegExpException|JSMin_UnterminatedStringException
     */
    protected function action($command)
    {
        // make sure we don't compress "a + ++b" to "a+++b", etc.
        if ($command === self::ACTION_DELETE_A_B && $this->b === ' ' && ($this->a === '+' || $this->a === '-')) {
            // Note: we're at an addition/substraction operator; the inputIndex
            // will certainly be a valid index
            if ($this->input[$this->input_index] === $this->a) {
                // This is "+ +" or "- -". Don't delete the space.
                $command = self::ACTION_KEEP_A;
            }
        }
        switch ($command) {
            case self::ACTION_KEEP_A:
                // 1
                $this->output .= $this->a;
                if ($this->kept_comment) {
                    $this->output = rtrim($this->output, "\n");
                    $this->output .= $this->kept_comment;
                    $this->kept_comment = '';
                }
                $this->last_byte_out = $this->a;
            // fallthrough intentional
            // no break
            case self::ACTION_DELETE_A:
                // 2
                $this->a = $this->b;
                if ($this->a === "'" || $this->a === '"') {
                    // string literal
                    $str = $this->a;
                    // in case needed for exception
                    for (;;) {
                        $this->output .= $this->a;
                        $this->last_byte_out = $this->a;
                        $this->a = $this->get();
                        if ($this->a === $this->b) {
                            // end quote
                            break;
                        }
                        if ($this->is_eof($this->a)) {
                            throw new Js_Min_unterminated_String_Exception("JSMin: Unterminated String at byte {$this->input_index}: {$str}");
                        }
                        $str .= $this->a;
                        if ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->last_byte_out = $this->a;
                            $this->a = $this->get();
                            $str .= $this->a;
                        }
                    }
                }
            // fallthrough intentional
            // no break
            case self::ACTION_DELETE_A_B:
                // 3
                $this->b = $this->next();
                if ($this->b === '/' && $this->is_regexp_literal()) {
                    $this->output .= $this->a . $this->b;
                    $pattern = '/';
                    // keep entire pattern in case we need to report it in the exception
                    for (;;) {
                        $this->a = $this->get();
                        $pattern .= $this->a;
                        if ($this->a === '[') {
                            for (;;) {
                                $this->output .= $this->a;
                                $this->a = $this->get();
                                $pattern .= $this->a;
                                if ($this->a === ']') {
                                    break;
                                }
                                if ($this->a === '\\') {
                                    $this->output .= $this->a;
                                    $this->a = $this->get();
                                    $pattern .= $this->a;
                                }
                                if ($this->is_eof($this->a)) {
                                    throw new Js_Min_unterminated_Reg_Exp_Exception('JSMin: Unterminated set in RegExp at byte ' . $this->input_index . ": {$pattern}");
                                }
                            }
                        }
                        if ($this->a === '/') {
                            // end pattern
                            break;
                            // while (true)
                        } elseif ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                            $pattern .= $this->a;
                        } elseif ($this->is_eof($this->a)) {
                            throw new Js_Min_unterminated_Reg_Exp_Exception("JSMin: Unterminated RegExp at byte {$this->input_index}: {$pattern}");
                        }
                        $this->output .= $this->a;
                        $this->last_byte_out = $this->a;
                    }
                    $this->b = $this->next();
                }
        }
    }
    /**
     * @return bool
     */
    protected function is_regexp_literal()
    {
        if (false !== strpos('(,=:[!&|?+-~*{;', $this->a)) {
            // we obviously aren't dividing
            return true;
        }
        if ($this->a === ' ' || $this->a === "\n") {
            $length = strlen($this->output);
            if ($length < 2) {
                // weird edge case
                return true;
            }
            // you can't divide a keyword
            if (preg_match('/(?:case|else|in|return|typeof)$/', $this->output, $m)) {
                if ($this->output === $m[0]) {
                    // odd but could happen
                    return true;
                }
                // make sure it's a keyword, not end of an identifier
                $char_before_keyword = substr($this->output, $length - strlen($m[0]) - 1, 1);
                if (!$this->is_alpha_num($char_before_keyword)) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Return the next character from stdin. Watch out for lookahead. If the character is a control character,
     * translate it to a space or linefeed.
     *
     * @return string
     */
    protected function get()
    {
        $c = $this->look_ahead;
        $this->look_ahead = null;
        if ($c === null) {
            // getc(stdin)
            if ($this->input_index < $this->input_length) {
                $c = $this->input[$this->input_index];
                $this->input_index += 1;
            } else {
                $c = null;
            }
        }
        if (ord((string) $c) >= self::ORD_SPACE || $c === "\n" || $c === null) {
            return $c;
        }
        if ($c === "\r") {
            return "\n";
        }
        return ' ';
    }
    /**
     * Does $a indicate end of input?
     *
     * @param string $a
     * @return bool
     */
    protected function is_eof($a)
    {
        return ord($a) <= self::ORD_LF;
    }
    /**
     * Get next char (without getting it). If is ctrl character, translate to a space or newline.
     *
     * @return string
     */
    protected function peek()
    {
        $this->look_ahead = $this->get();
        return $this->look_ahead;
    }
    /**
     * Return true if the character is a letter, digit, underscore, dollar sign, or non-ASCII character.
     *
     * @param string $c
     *
     * @return bool
     */
    protected function is_alpha_num($c)
    {
        return preg_match('/^[a-z0-9A-Z_\$\\\\]$/', $c) || ord($c) > 126;
    }
    /**
     * Consume a single line comment from input (possibly retaining it)
     */
    protected function consume_single_line_comment()
    {
        $comment = '';
        while (true) {
            $get = $this->get();
            $comment .= $get;
            if (ord($get) <= self::ORD_LF) {
                // end of line reached
                // if IE conditional comment
                if (preg_match('/^\/@(?:cc_on|if|elif|else|end)\b/', $comment)) {
                    $this->kept_comment .= "/{$comment}";
                }
                return;
            }
        }
    }
    /**
     * Consume a multiple line comment from input (possibly retaining it)
     *
     * @throws JSMin_UnterminatedCommentException
     */
    protected function consume_multiple_line_comment()
    {
        $this->get();
        $comment = '';
        for (;;) {
            $get = $this->get();
            if ($get === '*') {
                if ($this->peek() === '/') {
                    // end of comment reached
                    $this->get();
                    if (0 === strpos($comment, '!')) {
                        // preserved by YUI Compressor
                        if (!$this->kept_comment) {
                            // don't prepend a newline if two comments right after one another
                            $this->kept_comment = "\n";
                        }
                        $this->kept_comment .= '/*!' . substr($comment, 1) . "*/\n";
                    } elseif (preg_match('/^@(?:cc_on|if|elif|else|end)\b/', $comment)) {
                        // IE conditional
                        $this->kept_comment .= "/*{$comment}*/";
                    }
                    return;
                }
            } elseif ($get === null) {
                throw new Js_Min_unterminated_Comment_Exception("JSMin: Unterminated comment at byte {$this->input_index}: /*{$comment}");
            }
            $comment .= $get;
        }
    }
    /**
     * Get the next character, skipping over comments. Some comments may be preserved.
     *
     * @return string
     */
    protected function next()
    {
        $get = $this->get();
        if ($get === '/') {
            switch ($this->peek()) {
                case '/':
                    $this->consume_single_line_comment();
                    $get = "\n";
                    break;
                case '*':
                    $this->consume_multiple_line_comment();
                    $get = ' ';
                    break;
            }
        }
        return $get;
    }
}
class Js_Min_unterminated_String_Exception extends Exception
{
}
class Js_Min_unterminated_Comment_Exception extends Exception
{
}
class Js_Min_unterminated_Reg_Exp_Exception extends Exception
{
}