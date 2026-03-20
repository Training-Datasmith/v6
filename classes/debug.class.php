<?php

declare (strict_types=1);
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
/**
 * Debug controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Debug
{
    /**
     * Custom debug messages
     *
     * @var array of strings
     */
    private array $_custom = [];
    /**
     * Debug timer used to calc page load time
     *
     * @var float
     */
    private $_debug_timer = 0;
    /**
     * Display debug message
     */
    private bool $_display = true;
    /**
     * Enabled/disabled
     */
    private bool $_enabled = false;
    /**
     * Error messages
     *
     * @var array of strings
     */
    private array $_errors = [];
    /**
     * Messages
     *
     * @var array of strings
     */
    private $_messages = [];
    /**
     * SQL messages
     *
     * @var array of strings
     */
    private array $_sql = [];
    /**
     * Custom timers
     *
     * @var array of floats
     */
    private array $_timers = [];
    /**
     * XDebug enabled
     */
    private bool $_xdebug = false;
    /**
     * Debug collect sections flag
     *
     * @var bool
     */
    public $stream_into_session = true;
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;
    ##############################################
    final protected function __construct()
    {
        // Turn error reporting off as it is displayed in debugger mode only!
        ini_set('display_errors', false);
        // Show ALL errors & notices
        error_reporting(E_ALL);
        ini_set('ignore_repeated_errors', true);
        ini_set('ignore_repeated_source', true);
        // Enable HTML Error messages
        ini_set('html_errors', true);
        ini_set('docref_root', 'http://docs.php.net/manual/en/');
        ini_set('docref_ext', '.php');
        // Define the Error & Exception handlers
        set_error_handler($this->error_logger(...), ini_get('error_reporting'));
        set_exception_handler($this->exception_handler(...));
        // Enable debugger
        if (isset($GLOBALS['config']) && is_object($GLOBALS['config'])) {
            $this->_enabled = (bool) $GLOBALS['config']->get('config', 'debug');
            if (!$this->_enabled) {
                error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED));
            }
            $ip_string = $GLOBALS['config']->get('config', 'debug_ip_addresses');
            if (!empty($ip_string)) {
                if (strstr($ip_string, ',')) {
                    $ip_string = preg_replace('/\s+/', '', $ip_string);
                    $ip_addresses = explode(',', $ip_string);
                    if (!in_array(get_ip_address(), $ip_addresses)) {
                        $this->_enabled = false;
                    }
                } else if ($ip_string !== get_ip_address()) {
                    $this->_enabled = false;
                }
            }
        }
        //If its time to clear the cache
        if (!CC_IN_ADMIN && isset($_GET['debug-cache-clear'])) {
            $this->stream_into_session = false;
            $GLOBALS['cache']->clear();
            $GLOBALS['cache']->tidy();
            httpredir(current_page(['debug-cache-clear']));
        }
        //Check for xdebug
        if (extension_loaded('xdebug') && function_exists('xdebug_is_enabled')) {
            $this->_xdebug = xdebug_is_enabled();
        }
        $this->_debug_timer = $this->_get_time();
        // Check register_globals
        if (ini_get('register_globals')) {
            trigger_error('register_globals are enabled. It is highly recommended that you disable this in your PHP configuration, as it is a large security hole, and may wreak havoc.', E_USER_WARNING);
        }
        Sanitize::clean_globals();
    }
    public function __destruct()
    {
        if (!defined('CC_IN_SETUP') && $this->stream_into_session) {
            // Read the existing spool, filter out any empty entries, then append
            // this request's output. Write back with overwrite=true to avoid
            // merge_array() clobbering earlier entries when the key already exists.
            $debug_spool = array_filter((array) Session::get_instance()->get('debug_spool', 'system', []));
            $debug_spool[] = $this->display(true);
            Session::get_instance()->set('debug_spool', $debug_spool, 'system', true);
        } else {
            $this->display();
        }
        restore_error_handler();
        restore_exception_handler();
    }
    /**
     * Setup the instance (singleton)
     *
     * @return This instance
     */
    public static function get_instance(): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    //=====[ Public ]=======================================
    /**
     * Set a debug message
     *
     * @param string $message
     */
    public function debug_message($message): void
    {
        $this->_messages[] = $message;
    }
    /**
     * Set an SQL message
     *
     * @param string $type
     * @param string $message
     */
    public function debug_sql($type, $message, $cache, $source): bool
    {
        if (!$this->_enabled) {
            return false;
        }
        if (!is_null($type) && !is_null($message) && !empty($message)) {
            $tag = '';
            settype($message, 'array');
            if ($cache && $source) {
                // Request from cache and taken from cache
                $tag = 'CACHE READ';
                $colour = '008000';
            } elseif ($cache && !$source) {
                // Request from cache but taken from SQL!
                $tag = 'CACHE WRITE';
                $colour = 'FF6600';
            } elseif (!$cache && $source) {
                // NOT requested from cache but taken from cache
                $tag = 'CACHE READ - NOT REQUESTED!';
                $colour = 'FF0000';
            } elseif (!$cache && !$source) {
                $tag = 'NOT CACHED';
                $colour = '000';
            }
            if ($type == 'error' || preg_match('/^INSERT .*CubeCart_system_error_log/', $message[0])) {
                $tag = empty($tag) ? 'ERROR' : 'ERROR - ' . $tag;
                $colour = 'FF0000';
            }
            $this->_sql[$type][] = '<span style="color:#' . $colour . '">Hack: ' . str_pad((string) $this->_get_time(), 16, '0') . (isset($message[1]) ? ' --- Duration: ' . $message[1] : '') . ' [' . $tag . ']' . (!$cache && !$source ? '' : ' --- Key: sql.' . md5($message[0])) . '<br />' . htmlentities($message[0], ENT_COMPAT, 'UTF-8') . '</span>';
            return true;
        }
        return false;
    }
    /**
     * Add a message to the debug tail
     *
     * @param mixed $data
     * @param string $name
     * @return bool
     */
    public function debug_tail(&$data, $name = '')
    {
        $name = empty($name) ? count($this->_custom) : $name;
        if (!isset($this->_custom[$name])) {
            $this->_custom[$name] =& $data;
            return true;
        }
        return $this->debug_tail($data);
    }
    /**
     * Display debug
     *
     * @param bool $return
     * @param glue $string
     * @return bool
     */
    public function display($return = false, $glue = "\n")
    {
        // Cheeky hack for the w3c validator - we don't want it seeing the debug output
        if (strstr((string) $_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')) {
            $this->_enabled = false;
        }
        if ($this->_display && $this->_enabled) {
            $output[] = "<div style='font-family: \"Courier New\",Courier,monospace;font-size: 10px;border-bottom: 5px dashed silver;border-top: 5px dashed silver;margin: 0 0 50px 0;color: #000;background-color: #E7E7E7; clear: both; padding: 5px;'>";
            $output[] = '<h2>Debug Output - ' . $_SERVER['REQUEST_URI'] . '</h2>';
            $output[] = '<div>This can be disabled via &quot;Store Settings&quot; &raquo; &quot;Advanced&quot; (Tab) &raquo; &quot;Enable Debugging&quot;.</div>';
            $output[] = '<hr/>';
            // Display the PHP errors
            $output[] = '<strong>PHP</strong>:<br />' . htmlspecialchars(strip_tags($this->_error_display())) . '<hr size="1" />';
            //Get the super globals
            if (($ret = $this->_make_export_string('GET', merge_array(['Before Sanitise:' => $GLOBALS['RAW']['GET']], ['After Sanitise:' => $_GET]))) !== false) {
                $output[] = $ret;
            }
            if (($ret = $this->_make_export_string('POST', $_POST)) !== false) {
                $output[] = $ret;
            }
            if (isset($_SESSION) && !empty($_SESSION) && ($ret = $this->_make_export_string('SESSION', $_SESSION)) !== false) {
                $output[] = $ret;
            }
            if (($ret = $this->_make_export_string('COOKIE', merge_array(['Received:' => $_COOKIE], ['Sent:' => $GLOBALS['SENT_COOKIES']]))) !== false) {
                $output[] = $ret;
            }
            if (($ret = $this->_make_export_string('FILES', $_FILES)) !== false) {
                $output[] = $ret;
            }
            //Custom timers
            if (!empty($this->_timers)) {
                $output[] = '<strong>Timers</strong><br />';
                foreach ($this->_timers as $name => $timer) {
                    $output[] = '<strong>' . $name . '</strong>: ' . $timer['diff'] . '<br />';
                }
                $output[] = '<hr size="1" />';
            }
            // Display SQL Queries and Errors
            if (!empty($this->_sql)) {
                $output[] = '<strong>' . Database::get_instance()->get_db_engine() . '</strong><br />';
                if (!empty($this->_sql['query'])) {
                    $output[] = '<strong>Queries (' . count($this->_sql['query']) . ')</strong>:<br />';
                    $output[] = '<table>';
                    foreach ($this->_sql['query'] as $index => $query) {
                        if (!empty($query)) {
                            $output[] = '<tr><td style="text-align:right;padding:5px"><strong>' . ($index + 1) . '.</strong></td><td style="text-align:left;padding:5px">' . $query . '</td></tr>';
                        }
                    }
                    $output[] = '</table>';
                }
                if (!empty($this->_sql['error'])) {
                    $output[] = '<strong>Errors</strong>:<br />';
                    foreach ($this->_sql['error'] as $index => $error) {
                        if (!empty($error)) {
                            $sql_error = true;
                            $output[] = '<span style="color: #ff0000">[<strong>' . ($index + 1) . '</strong>] ' . strip_tags((string) $error) . '</span><br />';
                        }
                    }
                    if (!isset($sql_error)) {
                        $output[] = 'No Errors';
                    }
                }
                $output[] = '<hr size="1" />';
            }
            if (!empty($this->_messages)) {
                $output[] = '<strong>Debug Messages</strong>:<br />';
                foreach ($this->_messages as $key => $message) {
                    $output[] = '[' . $key . '] ' . $message . '<br />';
                }
                $output[] = '<hr size="1" />';
            }
            // Display logged variables
            foreach ($this->_custom as $name => $data) {
                if (empty($data)) {
                    $data = 'No data';
                }
                if (is_numeric($name)) {
                    $name = "customLog[{$name}]";
                }
                if (is_array($data)) {
                    ksort($data);
                    $data = '<pre>' . print_r($data, true) . '</pre>';
                }
                $output[] = '<strong>' . htmlentities($name, ENT_QUOTES, 'UTF-8') . '</strong>:<br />' . $data . '<hr size="1" />';
            }
            // Show some performance data
            $output[] = '<strong>Memory: Peak Usage / Max (%)</strong>:<br />' . $this->_debug_memory_usage(true) . '<hr size="1" />';
            // Show cache stats
            //We need another cache instance because of the destruct
            $cache = Cache::get_instance();
            $cache->status();
            $cache_state = $cache->status ? '<span style="color: #008000">' . $cache->status_desc . '</span>' : '<span style="color: #ff0000">' . $cache->status_desc . '</span>';
            $clear_cache_url = current_page(null, ['debug-cache-clear' => 'true']);
            $clear_cache = CC_IN_ADMIN ? '' : '[<a href="javascript: void(0)" onclick="javascript:window.opener.document.location.href=\'' . $clear_cache_url . '\'">Clear Cache</a>]';
            $output[] = '<strong>Cache (' . $cache->get_cache_system() . '): ' . $cache_state . '</strong><br />' . $cache->usage() . ' ' . $clear_cache . '<hr size="1" />';
            // Page render timer
            $output[] = '<strong>Page Load Time</strong>:<br />' . ($this->_get_time() - $this->_debug_timer) . ' seconds';
            if ($this->_xdebug && ini_get('xdebug.profiler_enable_trigger') == 1) {
                $output[] = ' [<a href="' . current_page(null, ['XDEBUG_PROFILE' => 'true']) . '">CacheGrind</a>]';
            }
            $output[] = '</div>';
            $content = implode($glue, $output);
            $this->_display = false;
            if ($return) {
                return $content;
            }
            $has_debug_spool = is_object($GLOBALS['session']) && $GLOBALS['session']->has('debug_spool');
            $debug_html = implode('', $has_debug_spool ? $GLOBALS['session']->get('debug_spool') : []) . $content;
            echo '<script type="text/javascript">
                function debugConsole(content) {
                    _cubecart_console = window.open("", "console:cubecart_debug", "width=1024,height=600,left=50,top=50,resizable,scrollbars=yes");
                    _cubecart_console.document.write(`<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head><title>CubeCart Debug Console</title><style>body{margin:0}</style></head><body>`+content+`</body></html>`);
                    _cubecart_console.blur();
                    window.focus();
                }
                const debug_data = `' . str_replace('`', '\`', implode('', $has_debug_spool ? $GLOBALS['session']->get('debug_spool') : []) . $content) . '`;
                debugConsole(debug_data);
                </script>';
            if ($has_debug_spool) {
                $GLOBALS['session']->set('debug_spool', null);
            }
        }
    }
    /**
     * End a custom timer
     *
     * The timer will be displayed on the debug display as well
     *
     * @return float
     */
    public function end_timer(string $name): int|float
    {
        if (isset($this->_timers[$name])) {
            $this->_timers[$name]['end'] = $this->_get_time();
            $this->_timers[$name]['diff'] = $this->_timers[$name]['end'] - $this->_timers[$name]['start'];
            return $this->_timers[$name]['diff'];
        }
        trigger_error('Timer not started (' . $name . ')', E_USER_WARNING);
        return 0;
    }
    /**
     * Error logger
     *
     * @param int $error_no
     * @param string $error_context
     */
    public function error_logger($error_no, string $error_string, string $error_file, string $error_line, $error_context = null): bool
    {
        $log = true;
        $can_log = isset($GLOBALS['config']) && is_object($GLOBALS['config']) && method_exists($GLOBALS['config'], 'get') && (bool) $GLOBALS['config']->get('config', 'debug');
        switch ($error_no) {
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $type = 'Deprecated';
                $log = $can_log;
                break;
            case E_CORE_ERROR:
                $type = 'Core Error';
                break;
            case E_CORE_WARNING:
                $type = 'Core Warning';
                $log = $can_log;
                break;
            case E_COMPILE_ERROR:
                $type = 'Compile Error';
                break;
            case E_COMPILE_WARNING:
                $type = 'Compile Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $type = 'Error';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'Notice';
                $log = $can_log;
                break;
            case E_PARSE:
                $type = 'Parse Error';
                break;
            case E_RECOVERABLE_ERROR:
                $type = 'Recoverable';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                $log = $can_log;
                break;
            case 'EXCEPTION':
                $type = 'Exception';
                break;
            default:
                $type = 'Unknown (' . $error_no . ')';
        }
        $error = '[<strong>' . $type . "</strong>] \t" . $error_file . ':' . $error_line . ' - ' . $error_string;
        $this->_errors[] = $error;
        if ($log) {
            $backtrace = debug_backtrace();
            array_shift($backtrace);
            ob_start();
            array_walk($backtrace, function (array $a): void {
                if (!empty($a['line'])) {
                    print $a['function'] . '() (' . basename((string) $a['file']) . ':' . $a['line'] . ")\n";
                }
            });
            $backtrace = ob_get_contents();
            ob_end_clean();
            $this->_write_error_log($error, $type, $backtrace);
        }
        return false;
    }
    /**
     * Error handler
     *
     * @param object $e
     */
    public function exception_handler($e): void
    {
        $message = "[<strong>Exception</strong>] \t" . $e->get_file() . ':' . $e->get_line() . ' - ' . $e->get_message();
        $this->_errors[] = $message;
        $backtrace = $e->get_trace();
        ob_start();
        array_walk($backtrace, function (array $a): void {
            if (!empty($a['line'])) {
                print $a['function'] . '() (' . basename((string) $a['file']) . ':' . $a['line'] . ")\n";
            }
        });
        $backtrace = ob_get_contents();
        ob_end_clean();
        if (empty($backtrace)) {
            $backtrace = 'No Backtrace.';
        }
        $this->_write_error_log($message, 'Exception', $backtrace);
    }
    /**
     * Start a custom timer
     *
     * @param string $name
     */
    public function start_timer($name): void
    {
        $this->_timers[$name]['start'] = $this->_get_time();
    }
    /**
     * Get/set the debug status
     *
     * @param bool $status
     * @return bool
     */
    public function status($status = null)
    {
        if (!is_null($status) && is_bool($status)) {
            $this->_enabled = $status;
        }
        return $this->_enabled;
    }
    /**
     * Supress display
     */
    public function supress(): void
    {
        $this->_display = false;
    }
    //=====[ Private ]=======================================
    /**
     * Get the byte size
     *
     * @return string
     */
    private static function _debug_get_bytes(string|bool $input)
    {
        return match (substr($input, -1, 1)) {
            'G' => substr($input, 0, strlen($input) - 1) * 1024 * 1024 * 1024,
            'M' => substr($input, 0, strlen($input) - 1) * 1024 * 1024,
            'K' => substr($input, 0, strlen($input) - 1) * 1024,
            default => $input,
        };
    }
    /**
     * Get memory usage
     */
    private function _debug_memory_usage(bool $peak = false): string
    {
        $mem_avail = ini_get('memory_limit');
        if ($this->_xdebug) {
            $mem_used = $peak ? xdebug_peak_memory_usage() : xdebug_memory_usage();
        } else {
            $mem_used = $peak ? memory_get_peak_usage() : memory_get_usage();
        }
        $mem_percent = round($mem_used / self::_debug_get_bytes($mem_avail) * 100, 2);
        $mem_used_hr = implode('', format_bytes($mem_used));
        return $mem_used_hr . ' / ' . $mem_avail . ' (' . $mem_percent . '%)';
    }
    /**
     * Make error message
     *
     * @param string $glue
     */
    private function _error_display($glue = '<br />'): string
    {
        if (!empty($this->_errors) && is_array($this->_errors)) {
            return implode($glue, $this->_errors);
        }
        return 'No Errors or Warnings';
    }
    /**
     * Get time using microtime or xdebug
     */
    private function _get_time(): float
    {
        if ($this->_xdebug) {
            return xdebug_time_index();
        }
        return microtime(true);
    }
    /**
     * Make a variable export string
     *
     * @param string $variable
     * @param int $left
     */
    private function _make_export($variable, int|float $left = 8): string
    {
        $output = '';
        foreach ($variable as $key => $value) {
            if ((string) $key == 'debug_spool') {
                continue;
            }
            if (is_array($value)) {
                $output .= '<div style="margin-left: ' . $left . 'px;">\'' . $key . '\' => ' . $this->_make_export($value, $left + 8) . '</div>';
            } else {
                $output .= '<div style="margin-left: ' . $left . 'px;">\'' . $key . '\' => ' . nl2br(htmlspecialchars((string) $value, ENT_NOQUOTES)) . '</div>';
            }
        }
        return $output;
    }
    /**
     * Makes an export string for debug
     *
     * @param array $variable
     * @return string/false
     */
    private function _make_export_string(string $name, $variable): string|false
    {
        $output = '<strong>' . $name . '</strong>:<br />';
        $values = $this->_make_export($variable);
        if (empty($values)) {
            $output .= 'Empty';
        } else {
            $output .= $this->_make_export($variable);
        }
        return $output . '<hr size="1" />';
    }
    /**
     * Write message to the error log in the DB
     */
    private function _write_error_log(string $message, string $type, string|bool $backtrace = ''): void
    {
        $url = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        if (isset($GLOBALS['db']) && $GLOBALS['db']->connected) {
            $log_days = is_object($GLOBALS['config']) && method_exists($GLOBALS['config'], 'get') ? $GLOBALS['config']->get('config', 'r_system_error') : 7;
            if (ctype_digit((string) $log_days) && $log_days > 0) {
                $GLOBALS['db']->insert('CubeCart_system_error_log', ['message' => $message, 'url' => $url, 'backtrace' => $backtrace, 'time' => time()]);
                if (execution_chance(2)) {
                    // 2% probability
                    $GLOBALS['db']->delete('CubeCart_system_error_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ' . $log_days . ' DAY))', 500);
                }
            } elseif (empty($log_days) || !$log_days) {
                $GLOBALS['db']->insert('CubeCart_system_error_log', ['message' => $message, 'url' => $url, 'backtrace' => $backtrace, 'time' => time()]);
            }
        } elseif ($type == 'Exception' || $type == E_PARSE) {
            echo $message;
        }
    }
}