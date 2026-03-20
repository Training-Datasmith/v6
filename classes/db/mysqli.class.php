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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
require CC_ROOT_DIR . '/classes/db/database.class.php';
/**
 * MySQLi database controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Database extends Database_Contoller
{
    ##############################################
    final protected function __construct($config)
    {
        $this->_db_engine = 'MySQLi';
        $dbport = isset($config['dbport']) && !empty($config['dbport']) ? $config['dbport'] : ini_get('mysqli.default_port');
        $dbsocket = isset($config['dbsocket']) && !empty($config['dbsocket']) ? $config['dbsocket'] : ini_get('mysqli.default_socket');
        $this->_db_connect_id = new mysqli($config['dbhost'], $config['dbusername'], $config['dbpassword'], $config['dbdatabase'], $dbport, $dbsocket);
        mysqli_options($this->_db_connect_id, MYSQLI_OPT_LOCAL_INFILE, true);
        if ($this->_db_connect_id->connect_error) {
            trigger_error($this->_db_connect_id->connect_error, E_USER_ERROR);
        } else {
            $this->connected = true;
        }
        $this->_prefix = $config['dbprefix'];
        $this->_setup();
        //Run the parent constructor
        parent::__construct();
    }
    /**
     * Setup the instance (singleton)
     *
     * @param $config array
     */
    public static function get_instance($config = ''): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }
    /**
     * Returns the rows affected
     *
     * @return array
     */
    public function affected()
    {
        return mysqli_affected_rows($this->_db_connect_id);
    }
    /**
     * Close the DB connection
     *
     * @return bool
     */
    public function close()
    {
        return $this->_db_connect_id->close();
    }
    /**
     * Is there an error?
     */
    public function error(): bool
    {
        $this->_errorno = (int) $this->_db_connect_id->errno;
        return (bool) $this->_errorno ? true : false;
    }
    /**
     * Error info
     */
    public function error_info(): string
    {
        return (string) $this->_db_connect_id->error;
    }
    /**
     * Get fields from a table
     *
     * @param string $table
     * @param bool $all
     * @return array
     */
    public function get_fields($table, $all = false)
    {
        if (isset($this->_allowed_columns[$table]) && is_array($this->_allowed_columns[$table])) {
            return $this->_allowed_columns[$table];
        }
        $query = "SHOW COLUMNS FROM {$this->_prefix}{$table};";
        //Try cache first
        if (($return = $this->_get_cached($query)) !== false) {
            $this->_allowed_columns[$table] = $return;
            return $return;
        }
        $return = [];
        if (($result = $this->_db_connect_id->query($query)) !== false) {
            while (($row = $result->fetch_assoc()) !== null) {
                $return[$row['Field']] = $row['Field'];
            }
        }
        //Write the cache
        $this->_write_cache($return, $query);
        $this->_allowed_columns[$table] = $return;
        return $return;
    }
    /**
     * Get the inserted ID
     *
     * @return id
     */
    public function insertid(): int
    {
        return (int) $this->_db_connect_id->insert_id;
    }
    /**
     * Get the server version
     *
     * @return string
     */
    public function server_version()
    {
        return $this->_db_connect_id->server_info;
    }
    /**
     * Make a string SQL safe
     *
     * @param string $value
     * @param string $quote
     * @return string
     */
    public function sql_safe($value, $quote = false)
    {
        $value = $this->_db_connect_id->escape_string(stripslashes((string) $value));
        return !$quote || is_null($value) ? $value : "'{$value}'";
    }
    //=====[ Private ]=======================================
    /**
     * Execute a query
     *
     * @param bool $cache
     * @param string $fetch
     *
     * @return bool
     */
    protected function _execute($cache = true, $fetch = true, $log = true)
    {
        $cache = $cache && !preg_match('#\b(' . $this->_cache_block_functions . ')\b#', (string) $this->_query) ?: false;
        $this->_found_rows = null;
        if (!empty($this->_query)) {
            $this->_result = [];
            // Don't read from cache in admin CP but write only for front end
            $cache = defined('ADMIN_CP') && ADMIN_CP ? false : $cache;
            if ($cache) {
                //Try getting the SQL cache
                $cache_check = $this->_get_cached($this->_query);
                if (is_array($cache_check) && (isset($cache_check['empty']) && $cache_check['empty']) && isset($cache_check['data'])) {
                    $this->_result = $cache_check['data'];
                    $this->_found_rows = sizeof($this->_result);
                    $this->_sql_debug($cache, true);
                    return true;
                }
                if ($cache_check) {
                    $this->_result = $cache_check;
                    $this->_found_rows = sizeof($this->_result);
                    $this->_sql_debug($cache, true);
                    return true;
                }
            }
            $this->_start_timer();
            $result = $this->_db_connect_id->query($this->_query);
            if ($result) {
                if (is_bool($result)) {
                    $this->_result = $result;
                } else {
                    $this->_found_rows = $result->num_rows;
                    $this->_result = [];
                    while ($row = $result->fetch_assoc()) {
                        $this->_result[] = $row;
                    }
                    $result->close();
                }
            }
            $this->_stop_timer();
            //If there is an error and its not because of system error
            if ($log && $this->error() && !str_contains($this->error_info(), 'CubeCart_system_error_log')) {
                $this->_log_error();
            }
            //Cache the result if needed
            if ($cache && $this->_write_cache($this->_result, $this->_query) === false) {
                $cache = false;
                // Query not cached for some reason. Check error log and cache status.
            }
            return !$this->_sql_debug($cache, false) ? true : false;
        }
        return false;
    }
    /**
     * Setup anything DB wise
     */
    private function _setup()
    {
        @mysqli_query($this->_db_connect_id, "SET SESSION sql_mode = ''");
        if (defined('CC_IN_SETUP') && CC_IN_SETUP) {
            // check MySQL Strict mode on upgrade/install
            $mysql_mode = $this->misc('SELECT @@sql_mode;');
            if (stristr((string) $mysql_mode[0]['@@sql_mode'], 'strict')) {
                die($GLOBALS['language']->setup['error_strict_mode']);
            }
            return false;
        }
        //Force UTF-8
        $this->_db_connect_id->set_charset('utf8mb4');
    }
}