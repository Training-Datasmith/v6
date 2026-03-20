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
 * Language controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Language
{
    /**
     * Exported Language File
     *
     * @var string
     */
    public $exported_lang_file = '';
    /**
     * Current language
     *
     * @var string
     */
    private $_language = '';
    /**
     * Custom language
     */
    private array $_language_custom = [];
    /**
     * Language data
     *
     * @var array
     */
    private $_language_data = [];
    /**
     * Language definitions
     *
     * @var array
     */
    private $_language_definitions = [];
    /**
     * Language definitions data
     *
     * @var array
     */
    private $_language_definition_data = [];
    /**
     * Language groups
     *
     * @var array
     */
    private $_language_groups = [];
    /**
     * Language strings
     *
     * @var array
     */
    private $_language_strings = [];
    /**
     * Language strings definitions
     *
     * @var array
     */
    private $_language_strings_def = [];
    public const LANG_REGEX = '#^([a-z]{2})\-([A-Z]{2})?$#';
    public const EMAIL_FILE = '#^email_(([a-z]{2})(\-[A-Z]{2})?(\-custom)?)\.[a-z]+(\.gz)?$#';
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;
    ##############################################
    final protected function __construct()
    {
        $d = [];
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache']) && $GLOBALS['cache']->exists('lang.domain.list')) {
            $d = $GLOBALS['cache']->read('lang.domain.list');
        } elseif (isset($GLOBALS['db']) && method_exists($GLOBALS['db'], 'select')) {
            if ($domains = $GLOBALS['db']->select('CubeCart_domains')) {
                foreach ($domains as $domain) {
                    if (empty($domain['domain'])) {
                        continue;
                    }
                    $d[$domain['domain']] = $domain['language'];
                }
                $GLOBALS['cache']->write($d, 'lang.domain.list');
            }
        }
        $url = parse_url(CC_STORE_URL);
        if (defined('ADMIN_CP') && ADMIN_CP == false && !empty($d) && isset($d[$url['host']]) && !empty($d[$url['host']])) {
            $this->_language = $d[$url['host']];
        } elseif (isset($GLOBALS['session'])) {
            //If the language is trying to be changed try to change it
            if ((isset($_POST['set_language']) && ($switch = $_POST['set_language']) || isset($_GET['set_language']) && $switch = $_GET['set_language']) && $this->_valid($switch)) {
                $customer_id = (int) $GLOBALS['session']->get_session_table_data('customer_id');
                if ($customer_id > 0) {
                    $GLOBALS['db']->update('CubeCart_customer', ['language' => $switch], ['customer_id' => $customer_id]);
                }
                $GLOBALS['session']->set('language', $switch, 'client');
                httpredir(current_page(['set_language']));
            } else if (!CC_IN_ADMIN && $GLOBALS['session']->has('language', 'client')) {
                $this->_language = $GLOBALS['session']->get('language', 'client');
            } elseif (CC_IN_ADMIN) {
                $admin_lang = $GLOBALS['session']->get('user_language', 'admin');
                $this->_language = !empty($admin_lang) ? $admin_lang : $GLOBALS['config']->get('config', 'default_language');
            } else {
                //Try the default config language
                $cl = $GLOBALS['config']->get('config', 'default_language');
                $this->_language = !empty($cl) && file_exists(CC_ROOT_DIR . '/language/' . $cl . '.xml') && $this->_valid($cl) ? $cl : 'en-GB';
                if (file_exists(CC_ROOT_DIR . '/language/' . $this->_language . '.xml')) {
                    //Set the language to the session
                    $GLOBALS['session']->set('language', $this->_language, 'client');
                } else {
                    trigger_error('No valid language found!', E_USER_ERROR);
                }
            }
        } else {
            $this->_language = 'en-GB';
        }
        if (!defined('CC_IN_SETUP') && $this->_language !== $GLOBALS['config']->get('config', 'default_language')) {
            header('X-Robots-Tag: noindex');
        }
        $GLOBALS['smarty']->assign('CURRENT_LANGUAGE', $this->_language);
        $this->load_lang();
    }
    /**
     * Setup the instance (singleton)
     */
    public static function get_instance($admin = false): self
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($admin);
        }
        return self::$_instance;
    }
    //=====[ Public ]=======================================
    /**
     * Magic get of a string value
     *
     * @return array/false
     */
    public function __get(string $name): mixed
    {
        $name = strtolower($name);
        return $this->_language_strings[$name] ?? false;
    }
    /**
     * Magic isset of a string value
     *
     * @return string
     */
    public function __isset(string $name)
    {
        return isset($this->_language_strings[strtolower($name)]);
    }
    /**
     * Magic set of a string value
     *
     * @param array $value
     */
    public function __set(string $name, mixed $value)
    {
        $name = strtolower($name);
        if (!isset($this->_language_strings[$name]) && is_array($value)) {
            $this->_language_custom[$name] = $name;
            $this->_language_strings[$name] = $value;
        }
    }
    /**
     * Magic unset of a string value
     */
    public function __unset(string $name)
    {
        $name = strtolower($name);
        if (isset($this->_language_custom[$name])) {
            unset($this->_language_strings[$name], $this->_language_custom[$name]);
        }
    }
    /**
     * Add strings to the language
     *
     * @param array $strings
     */
    public function add_strings($strings): void
    {
        if (!empty($strings) && is_array($strings)) {
            $this->_language_strings = merge_array($strings, $this->_language_strings);
        }
    }
    /**
     * Assign language to template
     */
    public function assign_lang(): void
    {
        $GLOBALS['smarty']->assign_by_ref('LANG', $this->_language_strings);
    }
    /**
     * Change language
     *
     * @param string $language
     */
    public function change($language): void
    {
        if ($this->_valid($language)) {
            $this->_language = $language;
            //Load the core language files
            $this->load_language_xml('lang.core');
            //Set the system locale
            $this->_set_locale();
        }
    }
    /**
     * Clone language for transaltion
     *
     * @param string $from
     * @param string $to
     */
    public function clone_module_language($from, string $language)
    {
        $from = CC_ROOT_DIR . '/' . $from;
        $to = str_replace('module.definitions.xml', $language . '.xml', $from);
        if (file_exists($from) && !file_exists($to) && ($content = simplexml_load_file($from)) !== false) {
            if (!isset($content->group) && !isset($content->group[0]->attributes()->name)) {
                return false;
            }
            $output = '<?xml version="1.0" encoding="UTF-8" ?>
<language version="1.0">
	<info>
		<title>' . $language . '</title>
		<code>' . $language . '</code>
		<character_set>utf-8</character_set>
		<version>1.0.0</version>
		<minVersion>5.0.0</minVersion>
		<maxVersion>6.*.*</maxVersion>
	</info>
	<translation>';
            if (!isset($content->group->string) && !isset($content->group[0]->attributes()->name)) {
                return false;
            }
            $output .= "\r\n\t\t<group name=\"" . $content->group[0]->attributes()->name . '">';
            foreach ($content->group->string as $key) {
                $output .= "\r\n\t\t\t<string name=\"" . $key->attributes()->name . '"><![CDATA[' . $key . ']]></string>';
            }
            $output .= "\r\n\t\t</group>\r\n\t</translation>\r\n</language>";
            return file_put_contents($to, $output);
        }
    }
    /**
     * Create language file
     *
     * @param array $array
     * @return array/false
     */
    public function create($array)
    {
        if (is_array($array) && isset($array['code'])) {
            // Check for required values
            if (preg_match(self::LANG_REGEX, (string) $array['code'])) {
                $xml = new XML();
                $xml->start_element('language', ['version' => '2.0']);
                $xml->start_element('info');
                // Info
                $xml->set_element('title', $array['title']);
                $xml->set_element('code', $array['code'], false, false);
                $xml->set_element('character_set', 'utf-8', false, false);
                $xml->set_element('version', '1.0.0', false, false);
                // Set min/max versions
                $xml->set_element('minVersion', '5.0.0', false, false);
                $xml->set_element('maxVersion', '6.*.*', false, false);
                $xml->set_element('default_currency', $array['currency_iso'], false, false);
                $xml->set_element('text_direction', $array['text_direction'], false, false);
                // Close
                $xml->end_element();
                $xml->end_element();
                //Write file
                $filename = CC_ROOT_DIR . '/language/' . $array['code'] . '.xml';
                return (bool) file_put_contents($filename, $xml->get_document());
            }
        }
        return false;
    }
    /**
     * Current language
     *
     * @return string
     */
    public function current()
    {
        return $this->_language;
    }
    /**
     * Delete a langauge
     *
     * @param string $code
     * @param string $path
     */
    public function delete_language(?string $code, $path = CC_LANGUAGE_DIR): bool
    {
        //Make sure the path is valid
        if (!$this->_check_path($path)) {
            trigger_error('Invalid language path ' . $path, E_USER_ERROR);
        }
        //Check to see if the path is the current language directory
        if ($path !== CC_LANGUAGE_DIR) {
            $path = append_ds($path);
        }
        if (!empty($code) && preg_match(self::LANG_REGEX, $code)) {
            $default_language = $GLOBALS['config']->get('config', 'default_language');
            // Correct customer preference
            $GLOBALS['db']->update('CubeCart_customer', ['language' => $default_language], ['language' => $code]);
            // Purge database
            $GLOBALS['db']->delete('CubeCart_lang_strings', ['language' => $code]);
            $GLOBALS['db']->delete('CubeCart_email_content', ['language' => $code]);
            // Delete language files
            $files = [];
            //Find every file file
            if (($search = glob($path . $code . '*', GLOB_NOSORT)) !== false) {
                $files = array_merge($files, $search);
            }
            //Find every email file
            if (($search = glob($path . 'email_' . $code . '*', GLOB_NOSORT)) !== false) {
                $files = array_merge($files, $search);
            }
            if (!empty($files)) {
                //Delete them
                foreach ($files as $file) {
                    unlink($file);
                }
                // Don't for get the flag!
                $flag_path = CC_LANGUAGE_DIR . 'flags/' . $code . '.png';
                if (file_exists($flag_path)) {
                    unlink($flag_path);
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Check to see if a product, document or category has been translated in all languages
     * @param string $type
     * @param int $id
     * @return bool
     */
    public function fully_translated($type, $id)
    {
        switch ($type) {
            case 'document':
                $data = ['table' => 'CubeCart_documents', 'id_column' => 'doc_id', 'language' => 'doc_lang', 'parent_id' => 'doc_parent_id', 'ignore_default' => false];
                break;
            case 'product':
                $data = ['table' => 'CubeCart_inventory_language', 'id_column' => 'product_id', 'language' => 'language', 'parent_id' => false, 'ignore_default' => true];
                break;
            case 'category':
                $data = ['table' => 'CubeCart_category_language', 'id_column' => 'cat_id', 'language' => 'language', 'parent_id' => false, 'ignore_default' => true];
                break;
        }
        // Break on false
        if (($languages = $this->list_languages()) !== false) {
            $result = true;
            foreach ($languages as $language) {
                // skip default language if languages are stores in same table
                if ($data['ignore_default'] && $language['code'] == $GLOBALS['config']->get('config', 'default_language')) {
                    continue;
                }
                $where = $data['parent_id'] ? '`' . $data['language'] . '` = \'' . $language['code'] . '\' AND (`' . $data['parent_id'] . '` = ' . $id . ' || `' . $data['id_column'] . '`= ' . $id . ')' : [$data['language'] => $language['code'], $data['id_column'] => $id];
                if ($result && !$GLOBALS['db']->select($data['table'], false, $where)) {
                    $result = false;
                }
            }
        }
        return $result;
    }
    /**
     * Get custom language strings
     *
     * @param string $group
     * @param string $language
     */
    public function get_custom($group, $language = ''): array
    {
        if (!empty($group)) {
            if (empty($language)) {
                $language = $this->_language;
            }
            if (($custom = $GLOBALS['db']->select('CubeCart_lang_strings', false, ['type' => $group, 'language' => $language])) !== false) {
                foreach ($custom as $string) {
                    $strings[$string['name']] = $string['value'];
                }
                natsort($strings);
                return $strings;
            }
        }
        return [];
    }
    /**
     * Get language data
     *
     * @param string $element
     * @return array/false
     */
    public function get_data($element = '')
    {
        if (empty($element)) {
            return $this->_language_data;
        }
        return $this->_language_data[$element] ?? false;
    }
    /**
     * Get language definitions
     *
     * @param string $group
     */
    public function get_definitions($group): array
    {
        if (!empty($group) && isset($this->_language_definitions[$group]) && isset($this->_language_definition_data[$group])) {
            foreach ($this->_language_definition_data[$group] as $name => $data) {
                $definition[$name] = array_merge($data, ['value' => $this->_language_definitions[$group][$name]]);
            }
            ksort($definition);
            return $definition;
        }
        return [];
    }
    /**
     * Get friendly module path from relative path
     *
     * @param string $path
     * @param bool $name_only
     * @return friendly path
     */
    public function get_friendly_module_path($path, $name_only = false): string
    {
        $path_parts = explode('/', $path);
        if ($name_only) {
            return strtolower($path_parts[2]);
        }
        return ucfirst($path_parts[0]) . ' - ' . ucfirst($path_parts[1]) . ' - ' . $path_parts[2];
    }
    /**
     * Get language groups
     *
     * @return array/false
     */
    public function get_groups(): array|false
    {
        //  if (!empty($this->_language_strings)) {
        if (!empty($this->_language_strings_def)) {
            // $this->_language_strings_def in method loadLang() defined
            if (empty($this->_language_groups)) {
                foreach ($this->_language_strings_def as $group => $strings) {
                    // $this->_language_strings_def in method loadLang() defined
                    //    foreach ($this->_language_strings as $group => $strings) {
                    $this->_language_groups[$group] = $group;
                }
                unset($group, $strings);
            }
            natsort($this->_language_groups);
            return $this->_language_groups;
        }
        return false;
    }
    /**
     * Get current language
     *
     * @return string
     */
    public function get_language()
    {
        return $this->_language;
    }
    /**
     * Get langauge information
     *
     * @param string $language
     * @return array/false
     */
    public function get_language_info($language)
    {
        $list = $this->list_languages();
        if (is_array($list) && !empty($language) && isset($list[$language])) {
            return $list[$language];
        }
        return false;
    }
    /**
     * Get all the language strings
     *
     * @return array/false
     */
    public function get_language_strings()
    {
        return !empty($this->_language_strings) ? $this->_language_strings : false;
    }
    /**
     * Get language strings
     *
     * @param string $group
     * @return array/false
     */
    public function get_strings($group = false): array|false
    {
        if (!empty($group)) {
            if (isset($this->_language_strings[$group])) {
                ksort($this->_language_strings[$group]);
                return $this->_language_strings[$group];
            }
            return false;
        }
        // return all strings?
        return (array) $this->_language_strings;
    }
    /**
     * Import email language
     *
     * @param string $source
     * @param string $path
     */
    public function import_email(?string $source, $path = CC_LANGUAGE_DIR, $content_type = ''): bool
    {
        //Make sure the path is valid
        if (!$this->_check_path($path)) {
            trigger_error('Invalid language path ' . $path, E_USER_ERROR);
        }
        //Check to see if the path is the current language directory
        if ($path !== CC_LANGUAGE_DIR) {
            $path = append_ds($path);
        }
        if (!empty($source) && preg_match(self::EMAIL_FILE, $source, $match)) {
            $file = $path . $source;
            if (file_exists($file)) {
                //Get the file data
                $data = file_get_contents($file);
                if (($gz = @gzuncompress($data)) !== false) {
                    $data = $gz;
                    unset($gz);
                }
                try {
                    $xml = new Simple_Xml_Element($data);
                    if ($xml->email) {
                        $traditional_oid_col = 'cart_order_id';
                        $config_oid_col = defined('CC_IN_SETUP') ? $traditional_oid_col : $GLOBALS['config']->get('config', 'oid_col');
                        $oid_col = empty($config_oid_col) ? $traditional_oid_col : $config_oid_col;
                        foreach ($xml->email as $email) {
                            if (!empty($content_type) && $content_type !== (string) $email->attributes()->name) {
                                continue;
                            }
                            if ($email->content) {
                                $record['content_type'] = (string) $email->attributes()->name;
                                $record['language'] = (string) $xml->attributes()->language;
                                $record['subject'] = str_replace('DATA.' . $traditional_oid_col, 'DATA.' . $oid_col, (string) $email->subject);
                                foreach ($email->content as $content) {
                                    // Plain text is auto-generated from HTML; skip legacy text entries
                                    if ((string) $content->attributes()->type === 'text') {
                                        continue;
                                    }
                                    // See GitHub #1511
                                    $record['content_' . $content->attributes()->type] = str_replace(['empty({$', '})}', 'DATA.' . $traditional_oid_col], ['empty($', ')}', 'DATA.' . $oid_col], trim((string) $content));
                                }
                                if ($GLOBALS['db']->select('CubeCart_email_content', ['content_id'], ['language' => $record['language'], 'content_type' => $record['content_type']], false, 1, false, false)) {
                                    $GLOBALS['db']->update('CubeCart_email_content', $record, ['language' => $record['language'], 'content_type' => $record['content_type']]);
                                } else {
                                    $GLOBALS['db']->insert('CubeCart_email_content', $record);
                                }
                            }
                            unset($record);
                        }
                        return true;
                    }
                } catch (Exception $e) {
                    trigger_error($e->get_message());
                    return false;
                }
            }
        }
        return false;
    }
    /**
     * Import language file if valid
     *
     * @param file $file
     * @param bool $overwrite
     * @return bool
     */
    public function import_language(array $file, $overwrite = false)
    {
        if (!preg_match('/.xml$/', (string) $file['name']['file'])) {
            trigger_error('Please upload a valid XML file.');
            return false;
        }
        $temp_name = $file['tmp_name']['file'];
        $destination = CC_LANGUAGE_DIR . $file['name']['file'];
        $file_content = file_get_contents($temp_name);
        // Validate file
        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($file_content);
        if (!$sxe) {
            foreach (libxml_get_errors() as $error) {
                trigger_error('Failed loading XML:' . $error->message);
            }
            return false;
        }
        try {
            $xml = new Simple_Xml_Element($file_content);
            if (empty($xml->info->title) || empty($xml->info->code)) {
                return false;
            }
        } catch (Exception $e) {
            trigger_error($e->get_message());
            return false;
        }
        // Check if file already exists and if we are allowed to overwrite
        if ($overwrite) {
            unlink($destination);
        } elseif (file_exists($destination)) {
            return false;
        }
        return move_uploaded_file($file['tmp_name']['file'], $destination);
    }
    /**
     * List availible languages
     *
     * @param bool $cache
     * @return array/false
     */
    public function list_languages($cache = true)
    {
        //Try cache first
        if ($cache && $GLOBALS['cache']->exists('lang.list')) {
            return $GLOBALS['cache']->read('lang.list');
        }
        //Get all langauge files
        if (($files = glob(CC_LANGUAGE_DIR . '*.{xml,gz}', GLOB_BRACE)) !== false) {
            $d = [];
            if (isset($GLOBALS['db']) && method_exists($GLOBALS['db'], 'select') && $domains = $GLOBALS['db']->select('CubeCart_domains')) {
                foreach ($domains as $domain) {
                    $d[$domain['language']] = $domain['domain'];
                }
            }
            $list = [];
            foreach ($files as $file) {
                // Get the language code from the filename
                $elements = explode('.', basename($file));
                //Validate the name
                if (preg_match(self::LANG_REGEX, $elements[0], $match)) {
                    //Get content
                    if (($xml = file_get_contents($file, true)) === false) {
                        continue;
                    }
                    if (empty($xml)) {
                        continue;
                    }
                    //Get the file extension
                    $ext = strtolower(substr(strrchr($file, '.'), 1));
                    //Make sure the headers for gz are there first
                    if ($ext == 'gz' && $xml[0] == 'x' && $xml[1] == "\x9c") {
                        if (($gz = gzuncompress($xml)) !== false) {
                            $xml = $gz;
                            unset($gz);
                        }
                    }
                    try {
                        $data = new Simple_Xml_Element($xml);
                        foreach ((array) $data->info as $key => $value) {
                            $c = trim((string) $data->info->code);
                            $list[$c][trim((string) $key)] = trim((string) $value);
                        }
                    } catch (Exception $e) {
                        trigger_error($e->get_message());
                        return false;
                    }
                    $list[$c]['domain'] = isset($d[$c]) && !empty($d[$c]) ? $d[$c] : '';
                    unset($data, $xml);
                }
            }
            if (!empty($list) && is_array($list)) {
                ksort($list);
                $GLOBALS['cache']->write($list, 'lang.list');
                return $list;
            }
        }
        return false;
    }
    /**
     * Load language definitions
     *
     * @param string $path
     * @param bool $cache
     */
    public function load_definitions(string $name, $path = CC_LANGUAGE_DIR, string $file_name = 'definitions.xml', $cache = true, $merge = true)
    {
        if (!$this->_check_path($path)) {
            trigger_error("Invalid language path: {$path} - {$name} - {$file_name}", E_USER_ERROR);
        }
        if ($path !== CC_LANGUAGE_DIR) {
            $path = append_ds($path);
        }
        // Load basic language string data into a multi-dimensional array
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache']) && ($GLOBALS['cache']->exists($name . '.definitions') && is_array($GLOBALS['cache']->read($name . '.definitions'))) && ($GLOBALS['cache']->exists($name . '.definition_data') && is_array($GLOBALS['cache']->read($name . '.definition_data')))) {
            $this->_language_definitions = $GLOBALS['cache']->read($name . '.definitions');
            $this->_language_definition_data = $GLOBALS['cache']->read($name . '.definition_data');
        } else {
            // Load language definitions
            $file = $path . $file_name;
            if (file_exists($file)) {
                $definition_data = $definition = [];
                try {
                    $xml = new Simple_Xml_Element(file_get_contents($file));
                    foreach ($xml->group as $group) {
                        $group_name = (string) $group->attributes()->name;
                        if (empty($group_name)) {
                            continue;
                        }
                        foreach ($group->string as $string) {
                            // Get attributes of the string definition
                            $attributes = $string->attributes();
                            if (empty($attributes->name)) {
                                continue;
                            }
                            //Loop through each attributes
                            foreach ($attributes as $attr_name => $attr_value) {
                                $data[(string) $attr_name] = (string) $attr_value;
                            }
                            $definition_data[$group_name][(string) $attributes->name] = $data;
                            // String was introduced in this version, or earlier
                            if (isset($attributes->deprecated) && !empty($attributes->deprecated) && version_compare((string) $attributes->deprecated, CC_VERSION, '<=')) {
                                continue;
                            }
                            // String has not been deprecated, so add this string to the list, and set the default value
                            $definition[$group_name][(string) $attributes->name] = (string) $string;
                        }
                        unset($group);
                    }
                } catch (Exception $e) {
                    trigger_error($e->get_message());
                    return false;
                }
                if (!empty($definition)) {
                    $GLOBALS['cache']->write($definition, $name . '.definitions');
                    $GLOBALS['cache']->write($definition_data, $name . '.definition_data');
                    $this->_language_definitions = $definition;
                    $this->_language_definition_data = $definition_data;
                }
                unset($attributes, $definition, $group, $string, $xml);
            }
        }
        if ($merge) {
            $this->_language_strings = merge_array($this->_language_definitions, $this->_language_strings);
        } else {
            $this->_language_strings = $this->_language_definitions;
        }
    }
    /**
     * Load language
     */
    public function load_lang(): void
    {
        //Load the core language files
        $this->load_definitions('lang.core');
        $this->_language_strings_def = $this->_language_strings;
        // Admin Langs - show default core groups only
        $this->load_language_xml('lang.core');
        //Set the system locale
        $this->_set_locale();
    }
    /**
     * Load language XML file(s)
     *
     * @param string $language
     * @param string $path
     * @param bool $merge
     * @param bool load_custom
     * @return array/false
     */
    public function load_language_xml(string $name, $language = '', $path = CC_LANGUAGE_DIR, $merge = true, $load_custom = true)
    {
        $language = empty($language) ? $this->_language : $language;
        if (!$this->_check_path($path)) {
            trigger_error('Invalid language path ' . $path, E_USER_ERROR);
        }
        if ($path !== CC_LANGUAGE_DIR) {
            $path = append_ds($path);
        }
        $cache_name_info = 'lang.info.' . $language;
        $cache_name_strings = 'lang.' . $name . '.xml.' . $language;
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache']) && ($GLOBALS['cache']->exists($cache_name_strings) && $GLOBALS['cache']->exists($cache_name_info) && is_array($GLOBALS['cache']->read($cache_name_strings)) && is_array($GLOBALS['cache']->read($cache_name_info)))) {
            $strings = $GLOBALS['cache']->read($cache_name_strings);
            $this->_language_data = $GLOBALS['cache']->read($cache_name_info);
        } else {
            $strings = [];
            $data = $this->_extract_xml($path . $language);
            if (!empty($data)) {
                try {
                    $xml = new Simple_Xml_Element($data);
                    if (!empty($xml)) {
                        if (!empty($xml->info)) {
                            foreach ((array) $xml->info as $key => $value) {
                                $lang_data[$key] = (string) $value;
                            }
                            $GLOBALS['cache']->write($lang_data, $cache_name_info);
                            $this->_language_data = $lang_data;
                        }
                        switch (floor((float) $xml->attributes()->version)) {
                            case 2:
                                // New format - Similar layout to the definition file
                                if ($xml->translation && $xml->translation->group) {
                                    foreach ($xml->translation->group as $groups) {
                                        $group = (string) $groups->attributes()->name;
                                        foreach ($groups->string as $string) {
                                            $xml_name = $string->attributes()->name;
                                            $strings[$group][(string) $xml_name] = trim((string) $string);
                                        }
                                    }
                                    unset($groups, $group, $xml_name, $string);
                                }
                                break;
                            default:
                                trigger_error('Language format error - exiting.', E_USER_WARNING);
                                die;
                        }
                    }
                } catch (Exception $e) {
                    trigger_error($e->get_message());
                    return false;
                }
                // Load custom strings from database
                if ($load_custom && isset($GLOBALS['db']) && ($custom = $GLOBALS['db']->select('CubeCart_lang_strings', false, ['language' => $language])) !== false) {
                    foreach ($custom as $string) {
                        $strings[(string) $string['type']][(string) $string['name']] = $string['value'];
                    }
                }
            }
            unset($custom, $data, $string, $xml);
            if (!empty($this->_language_strings)) {
                $GLOBALS['cache']->write($strings, $cache_name_strings);
            }
        }
        if ($merge && !empty($strings)) {
            $this->_language_strings = merge_array($this->_language_strings, $strings);
        }
        return !empty($strings) ? $strings : false;
    }
    /**
     * Save XML file
     *
     * @param string $language
     * @param bool $compress
     * @param bool $replace
     * @param string $path
     * @return bool
     */
    public function save_language_xml(?string $language, $compress = false, $replace = false, $path = CC_LANGUAGE_DIR)
    {
        if (!$this->_check_path($path)) {
            trigger_error('Invalid language path ' . $path, E_USER_ERROR);
        }
        if ($path !== CC_LANGUAGE_DIR) {
            $path = append_ds($path);
        }
        if (!empty($language)) {
            // Load in existing file
            $source = $path . $language . '.xml';
            $this->exported_lang_file = $replace ? $source : $path . $language . '-custom.xml';
            $strings = [];
            if (file_exists($source)) {
                $data = file_get_contents($source);
                try {
                    $xml = new Simple_Xml_Element($data);
                    foreach ($xml->info as $values) {
                        foreach ($values as $key => $value) {
                            $info[$key] = $value;
                        }
                    }
                    if ($xml->translation && $xml->translation->group) {
                        foreach ($xml->translation->group as $groups) {
                            $group = $groups->attributes()->name;
                            foreach ($groups->string as $string) {
                                $name = $string->attributes()->name;
                                $strings[(string) $group][(string) $name] = $string;
                            }
                        }
                    }
                } catch (Exception $e) {
                    trigger_error($e->get_message());
                    return false;
                }
                unset($data, $xml);
                // Fetch Database Results
                if (($custom = $GLOBALS['db']->select('CubeCart_lang_strings', false, ['language' => $language])) !== false) {
                    foreach ($custom as $row) {
                        $strings[$row['type']][$row['name']] = $row['value'];
                    }
                } else {
                    trigger_error('No custom strings exist', E_USER_NOTICE);
                }
                $xml = new XML();
                $xml->start_element('language', ['version' => '2.0']);
                $xml->start_element('info');
                foreach ($info as $key => $value) {
                    $xml->set_element($key, $value, false, false);
                }
                $xml->end_element();
                $xml->start_element('translation');
                foreach ($strings as $group => $values) {
                    $xml->start_element('group', ['name' => $group]);
                    foreach ($values as $name => $value) {
                        $xml->set_element('string', $value, ['name' => $name]);
                    }
                    $xml->end_element();
                }
                $xml->end_element();
                $xml->end_element();
                if ($compress) {
                    $output = gzencode($xml->get_document(), 9, FORCE_GZIP);
                    $this->exported_lang_file .= '.gz';
                } else {
                    $output = $xml->get_document();
                }
                return (bool) file_put_contents($this->exported_lang_file, $output);
            }
        }
        return false;
    }
    /**
     * Sets all the LANG variables in the templates
     */
    public function set_template(): void
    {
        $lang_data = $this->get_data();
        //Assign left to right or right to left
        $text_dir = $lang_data['text-direction'] ?? 'ltr';
        $GLOBALS['smarty']->assign('TEXT_DIRECTION', $text_dir);
        //Assign character set
        $char_set = $lang_data['character_set'] ?? 'utf-8';
        $GLOBALS['smarty']->assign('CHARACTER_SET', $char_set);
        //Assign all language values
        $this->assign_lang();
    }
    /**
     * Translate a category
     *
     * @param array $category
     * @return array/false
     */
    public function translate_category(&$category)
    {
        if (!empty($category)) {
            if ($this->_language != $GLOBALS['config']->get('config', 'default_language')) {
                if (($translation = $GLOBALS['db']->select('CubeCart_category_language', false, ['cat_id' => $category['cat_id'], 'language' => $this->_language])) !== false) {
                    $category = array_merge($category, $translation[0]);
                }
            }
            return $category;
        }
        return false;
    }
    /**
     * Translate a document
     *
     * @param array $document
     * @return array/false
     */
    public function translate_document(&$document)
    {
        if (!empty($document)) {
            if ($this->_language != $GLOBALS['config']->get('config', 'default_language')) {
                if (($translation = $GLOBALS['db']->select('CubeCart_documents', false, ['doc_parent_id' => $document['doc_id'], 'doc_lang' => $this->_language])) !== false) {
                    $document = array_merge($document, $translation[0]);
                }
            }
            return $document;
        }
        return false;
    }
    /**
     * Translate a product
     *
     * @param array $product
     * @return array/false
     */
    public function translate_product(&$product)
    {
        if (!empty($product)) {
            if ($this->_language != $GLOBALS['config']->get('config', 'default_language')) {
                if (($translation = $GLOBALS['db']->select('CubeCart_inventory_language', false, ['product_id' => (int) $product['product_id'], 'language' => $this->_language])) !== false) {
                    $product = array_merge($product, $translation[0]);
                }
            }
            return $product;
        }
        return false;
    }
    //=====[ Private ]=======================================
    /**
     * Checks to make sure the path passed in is valid
     *
     * @param string $path
     * @return bool
     */
    private function _check_path($path)
    {
        if (empty($path)) {
            return false;
        }
        //If the path is the Language directory it should already be valid
        if ($path == CC_LANGUAGE_DIR) {
            return true;
        }
        //Append the DS if needed
        $path = append_ds($path);
        return is_dir($path) && file_exists($path);
    }
    /**
     * Extract XML information
     *
     * @return array/false
     */
    private function _extract_xml(string $language): string|false
    {
        if (($files = glob($language . '*{-custom,}.xml*', GLOB_BRACE | GLOB_NOSORT)) !== false && !empty($files)) {
            $merged_addon_strings = '<?xml version="1.0"?><language version="2.0">';
            foreach ($files as $file) {
                if (str_ends_with($file, '.gz')) {
                    // Extract GZipped content
                    $xml_data = simplexml_load_file(gzuncompress($file . '.gz'));
                } else {
                    $xml_data = simplexml_load_file($file);
                }
                if (is_object($xml_data->info)) {
                    foreach ($xml_data->info as $element) {
                        $merged_addon_strings .= $element->as_xml();
                    }
                }
                $merged_addon_strings .= '<translation>';
                if (is_object($xml_data->translation->group)) {
                    foreach ($xml_data->translation->group as $element) {
                        $merged_addon_strings .= $element->as_xml();
                    }
                }
                if (is_object($xml_data->translation->translate)) {
                    foreach ($xml_data->translation->translate as $element) {
                        $merged_addon_strings .= $element->as_xml();
                    }
                }
                $merged_addon_strings .= '</translation>';
            }
            $merged_addon_strings .= '</language>';
            return !empty($merged_addon_strings) ? $merged_addon_strings : false;
        }
        return false;
    }
    /**
     * Set Locale
     */
    private function _set_locale(): void
    {
        setlocale(LC_ALL, 'en_GB.UTF-8');
    }
    /**
     * Validate language
     *
     * @return bool
     */
    private function _valid(string $language)
    {
        if (!preg_match(self::LANG_REGEX, $language)) {
            return false;
        }
        return file_exists(CC_LANGUAGE_DIR . $language . '.xml');
    }
}