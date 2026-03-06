<?php

declare(strict_types=1);
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
 *
 * @tutorial
 * To call hooks, use the following:
 *   foreach ($hooks->load('HOOK_TRIGGER') as $hook) include $hook;
 * Note that all hooks will be included into the current scope
 */

/**
 * Hook controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class HookLoader
{
    /**
     * Enable/disable
     */
    private bool $_enabled  = true;
    /**
     * Hook path
     *
     * @var path
     */
    private $_hook_dir  = false;
    /**
     * Code snippet path
     *
     * @var path
     */
    private $_snippet_dir  = false;
    /**
     * Code snippet file prefix
     *
     * @var path
     */
    private $_snippet_prefix  = 'snippet_';
    /**
     * Hook list
     */
    private array $_hook_list  = [];
    /**
     * Code snippet list
     *
     * @var array
     */
    private $_snippet_list  = [];
    /**
     * Array of the currently loaded language files for hooks
     * to help stop repeated lang loads
     */
    private array $_loaded_lang = [];
    /**
     * Plugin list
     *
     * @var array
     */
    private $_plugin_list = [];

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    final protected function __construct()
    {
        // Define the plugins directory
        $this->_hook_dir = CC_ROOT_DIR.'/modules/plugins';
        // Define the code snippets directory
        $this->_snippet_dir = CC_ROOT_DIR.'/includes/extra';
        // Generate a list of all hooks
        $this->_build_hooks_list(null, true);
        $this->_build_code_snippet_list(null, true);
    }

    /**
     * Setup the instance (singleton)
     */
    public static function getInstance(): self
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Delete code snippet include file
     *
     * @param int/string $unique_id
     * @return bool
     */
    public function delete_snippet_file($unique_id = '')
    {
        if (is_numeric($unique_id)) {
            $unique_id = $this->_get_unique_id($unique_id);
        }

        if (!empty($unique_id)) {
            $full_path = $this->_snippet_dir.'/'.$this->_snippet_prefix.md5((string) $unique_id).'.php';
            if (file_exists($full_path)) {
                return unlink($full_path);
            }
            return false;
        }
        return false;
    }

    /**
     * Enable/disable hooks
     *
     * @param bool $enable
     */
    public function enable($enable = true): void
    {
        $this->_enabled = (bool)$enable;
    }

    /**
     * Import code snippets
     *
     * @param array file
     */
    public function import_code_snippets(array $file): bool
    {
        if (file_exists($file['tmp_name'])) {
            if ($file['size'] > 0) {
                if (in_array($file['type'], ['application/x-zip', 'application/zip'])) {
                    $zip = new ZipArchive();
                    if ($zip->open($file['tmp_name']) === true) {
                        $contents = $zip->getFromIndex(0);
                    } else {
                        trigger_error('Error: Failed to read zip file.', E_USER_NOTICE);
                    }
                } else {
                    $contents = file_get_contents($file['tmp_name']);
                }
                if (empty($contents)) {
                    trigger_error('Error: No content found for code snippet.', E_USER_NOTICE);
                } else {
                    try {
                        $xml   = new simpleXMLElement($contents);
                        foreach ($xml->snippets->snippet as $snippet) {
                            $record = [
                                'author'  => $xml->info->author,
                                'enabled'  => $snippet->enabled,
                                'description' => $snippet->description,
                                'hook_trigger' => $snippet->hook_trigger,
                                'php_code'  => base64_encode($snippet->php_code),
                                'version'  => $snippet->version,
                                'priority'  => $snippet->priority,
                            ];

                            if ($GLOBALS['db']->select('CubeCart_code_snippet', ['snippet_id'], ['unique_id' => $snippet->unique_id])) {
                                $GLOBALS['db']->update('CubeCart_code_snippet', $record, ['unique_id' => $snippet->unique_id]);
                            } else {
                                $record['unique_id'] = $snippet->unique_id;
                                $GLOBALS['db']->insert('CubeCart_code_snippet', $record);
                            }
                            $this->delete_snippet_file($snippet->unique_id);
                        }
                        return true;
                    } catch (Exception $e) {
                        trigger_error($e->getMessage());
                    }
                }
            } else {
                trigger_error('Error: Code snippet import failed as the file is '.$file['size'].' bytes in size.', E_USER_NOTICE);
            }
        } else {
            trigger_error("Error: Code snippet import for '".$file['tmp_name']."' doesn't exist.", E_USER_NOTICE);
        }
        return false;
    }

    /**
     * Install new plugin
     *
     * @param string $plugin
     */
    public function install($plugin): bool
    {
        if (!empty($plugin)) {
            $this->_plugin_name($plugin);
            $file = $this->_hook_dir.'/'.$plugin.'/'.'config.xml';
            if (file_exists($file)) {
                // Read each XML file, check contents, and update/add to database
                try {
                    $xml   = new simpleXMLElement(file_get_contents($file));
                    $allowed_hooks = [];
                    foreach ($xml->hooks->hook as $hook) {
                        // Check if the hook already exists
                        array_push($allowed_hooks, (string)$hook->attributes()->trigger);
                        $check = $GLOBALS['db']->select('CubeCart_hooks', false, ['plugin' => $plugin, 'trigger' => (string)$hook->attributes()->trigger]);

                        $record = [
                            'plugin' => $plugin,
                            'hook_name' => (string)$hook,
                            'trigger' => (isset($check[0]['trigger']) && !empty($check[0]['trigger'])) ? $check[0]['trigger'] : (string)$hook->attributes()->trigger,
                            'priority' => (isset($check[0]['priority']) && !empty($check[0]['priority'])) ? $check[0]['priority'] : (int)$hook->attributes()->priority,
                            'filepath' => (isset($check[0]['filepath']) && !empty($check[0]['filepath'])) ? $check[0]['filepath'] : (string)$hook->file,
                        ];

                        if ($check) {
                            $GLOBALS['db']->update('CubeCart_hooks', $record, ['plugin' => $plugin, 'trigger' => (string)$hook->attributes()->trigger]);
                        } else {
                            $record['enabled'] = (int)$hook->attributes()->enabled;
                            $GLOBALS['db']->insert('CubeCart_hooks', $record);
                        }
                    }
                    // remove hooks not allowed
                    $GLOBALS['db']->misc('DELETE FROM `'.$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_hooks` WHERE `plugin` = '".$plugin."' AND `trigger` NOT IN ('".implode("','", $allowed_hooks)."')");
                    return true;
                } catch (Exception $e) {
                    trigger_error($e->getMessage());
                }
            }
        }
        return false;
    }

    /**
     * Check hook is enabled
     *
     * @param string $trigger
     * @param string $plugin
     * @return array
     */
    public function is_enabled($trigger, $plugin): bool
    {
        if (!empty($trigger) && !empty($plugin)) {
            $result = $GLOBALS['db']->select('CubeCart_hooks', ['enabled'], ['trigger' => $trigger, 'plugin' => $plugin]);
            if ($result[0]['enabled'] == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load hook
     *
     * @param string $trigger
     */
    public function load(?string $trigger): array
    {
        $return = [];

        if ($GLOBALS['config']->get('config', 'safe_mode') === true) {
            return $return;
        }

        if ($GLOBALS['config']->get('config', 'safe_mode') !== 'hooks' && $this->_enabled && !empty($trigger) && !empty($this->_hook_list)) {
            // Find all registered hooks
            if (is_array($this->_hook_list) && isset($this->_hook_list[$trigger]) && !empty($this->_hook_list[$trigger])) {
                // Load hooks for plugins
                foreach ($this->_hook_list[$trigger] as $hook) {
                    $this->_plugin_name($hook['plugin']);
                    $this->_plugin_language($hook['plugin']);
                    $hook['filepath'] = (!empty($hook['filepath'])) ? str_replace('/', '/', $hook['filepath']) : 'hooks/'.$trigger.'.php';
                    self::_security_check($hook['filepath']);
                    if (file_exists($this->_hook_dir.'/'.$hook['plugin'].'/'.$hook['filepath'])) {
                        $include[] =
                            [
                            'fullpath' => $this->_hook_dir.'/'.$hook['plugin'].'/'.$hook['filepath'],
                            'priority' => (int)$hook['priority'],
                            ];
                    } else {
                        trigger_error("Error: Hook '".$hook['plugin'].'/'.$hook['filepath']."' was not found", E_USER_NOTICE);
                    }
                }
            }
        }

        // Load hook for code snippets
        if ($GLOBALS['config']->get('config', 'safe_mode') !== 'snippets' && $this->_snippet_list) {
            foreach ($this->_snippet_list as $snippet) {
                if ($snippet['hook_trigger'] == $trigger) {
                    $file_name = $this->_snippet_dir.'/'.$this->_snippet_prefix.md5((string) $snippet['unique_id']).'.php';
                    if (file_exists($file_name)) {
                        $include[] =
                            [
                            'fullpath' => $file_name,
                            'priority' => (int)$snippet['priority'],
                            ];
                    } else {
                        if (file_put_contents($file_name, base64_decode((string) $snippet['php_code']))) {
                            $include[] =
                                [
                                'fullpath' => $file_name,
                                'priority' => (int)$snippet['priority'],
                                ];
                        } else {
                            trigger_error("Error: Failed to write code snippet for '".$snippet['description']."'", E_USER_NOTICE);
                        }
                    }
                }
            }
        }

        if (isset($include) && is_array($include)) {
            // sort $include based on priority
            uasort($include, cmpmc(...));
            foreach ($include as $inc) {
                $return[] = $inc['fullpath'];
            }
        }
        return $return;
    }

    /**
     * Scan for all plugs
     *
     * @param string $dir
     * @param bool $enabled
     */
    public function scan_all_plugins($dir = 'plugins', $enabled = false): array
    {
        $plugins = [];
        $dir = ($dir == 'plugins') ? $this->_hook_dir : $dir;
        if (($folders = glob($dir.'/'.'*', GLOB_ONLYDIR)) !== false) {
            foreach ($folders as $folder) {
                $basename = basename($folder);

                if ($enabled) {
                    $plugin = $GLOBALS['config']->get($basename);
                    if (!((bool)($plugin['status'] ?? false))) {
                        continue;
                    }
                }

                $plugins[$basename] = [
                    'plugin' => $basename,
                    'name'  => str_replace('_', ' ', $basename),
                ];
            }
            return $plugins;
        }
        return [];
    }

    /**
     * Scan for new plugins and install
     */
    public function scan_plugins(): bool
    {
        // Scan the plugins directory for config files, and adds the hooks to the database if they don't already exist
        if (($files = glob($this->_hook_dir.'/'.'*', GLOB_ONLYDIR | GLOB_NOSORT)) !== false) {
            foreach ($files as $file) {
                $this->install(basename($file));
            }
            return true;
        }
        return false;
    }

    /**
     * Uninstall plugin
     *
     * @param string $plugin
     * @return bool
     */
    public function uninstall($plugin)
    {
        if (!empty($plugin)) {
            return $GLOBALS['db']->delete('CubeCart_hooks', ['plugin' => $plugin]);
        }
    }

    //=====[ Private ]=======================================
    /**
     * Build hook list
     *
     * @param string $trigger
     */
    private function _build_code_snippet_list($trigger = null, bool $enabled_only = true): bool
    {
        $where = [];
        if (!is_null($trigger) && !empty($trigger)) {
            $where['hook_trigger'] = $trigger;
        }
        if ($enabled_only) {
            $where['enabled'] = '1';
        }

        if ($snippets = $GLOBALS['db']->select('CubeCart_code_snippet', false, $where, ['priority' => 'ASC'])) {
            $this->_snippet_list = $snippets;
            return true;
        }
        return false;
    }

    /**
     * Build hook list
     *
     * @param string $trigger
     */
    private function _build_hooks_list($trigger = null, bool $enabled_only = true): bool
    {
        $where = [];
        if (!is_null($trigger) && !empty($trigger)) {
            $where['trigger'] = $trigger;
        }
        if ($enabled_only) {
            $where['enabled'] = '1';
        }

        if (($hooks = $GLOBALS['db']->select('CubeCart_hooks', false, $where, ['priority' => 'ASC'])) !== false) {
            foreach ($hooks as $hook) {
                self::_security_check($hook['filepath']);
                $this->_plugin_name($hook['plugin']);
                $this->_hook_list[$hook['trigger']][$hook['plugin']] = $hook;
            }
            return true;
        }
        return false;
    }

    /**
     * Get hook unique ID
     *
     * @param int code snippet id
     * @return string/false
     */
    private function _get_unique_id($snippet_id)
    {
        if ($id = $GLOBALS['db']->select('CubeCart_code_snippet', ['unique_id'], ['snippet_id' => $snippet_id])) {
            return $id[0]['unique_id'];
        }
        return false;
    }

    /**
     * Setup plugin language
     */
    private function _plugin_language(string $plugin): void
    {
        $lang_dir = $this->_hook_dir.'/'.$plugin.'/'.'language';
        if (!isset($this->_loaded_lang[$plugin])) {
            if (file_exists($lang_dir)) {
                $GLOBALS['language']->loadDefinitions($plugin, $lang_dir, 'module.definitions.xml');
                $strings = $GLOBALS['language']->loadLanguageXML($plugin, '', $lang_dir);

                unset($strings);
                $this->_loaded_lang[$plugin] = true;
                $GLOBALS['language']->assignLang(); // Make the language strings fresh
            }
        }
    }

    /**
     * Setup plugin name
     *
     * @param string $plugin_name
     */
    private function _plugin_name(&$plugin_name): void
    {
        $plugin_name = preg_replace('#[^a-z0-9]#iU', '_', $plugin_name);
        if (!in_array($plugin_name, $this->_plugin_list)) {
            $this->_plugin_list[] = $plugin_name;
            ini_set('include_path', ini_get('include_path').CC_PS.$this->_hook_dir.'/'.$plugin_name);
        }
    }

    /**
     * Check plugin
     *
     * @param string $filename
     */
    private static function _security_check(&$filename): bool
    {
        $find  = ['#^[^a-z0-9.\\\\/_]$#iU', '#(/+)|(\\\+)#', '#\.{1,2}/#'];
        $replace = ['', '/', ''];
        $filename = preg_replace($find, $replace, $filename);
        $filename = ltrim((string) $filename, '/');
        return true;
    }
}
