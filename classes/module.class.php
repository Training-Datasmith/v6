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
 * Module controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Module
{
    /**
     * Module settings
     *
     * @var array
     */
    public $_settings;
    /**
     * Module content
     *
     * @var string
     */
    private $_content;
    /**
     * Module info
     *
     * @var array
     */
    private $_info = [];
    /**
     * Module local name
     *
     * @var string
     */
    private $_local_name;
    /**
     * Module name
     */
    private ?string $_module_name = null;
    /**
     * Module package file
     */
    private string $_package_file = 'package.conf.inc';
    /**
     * Module config dile
     */
    private string $_package_xml = 'config.xml';
    /**
     * Module path
     *
     * @var string
     */
    private $_path;
    /**
     * RAW Post Array
     *
     * @var array of strings
     */
    private array $_rawvarsout = [];
    /**
     * Module language strings
     *
     * @var array of strings
     */
    private $_strings;
    /**
     * Taxes
     *
     * @var array
     */
    private $_taxes;
    /**
     * Template data
     *
     * @var array
     */
    private $_template_data = [];
    ##############################################
    /**
     * @param string $template
     */
    public function __construct(
        $path = false,
        $local_name = false,
        /**
         * Template to load in the module
         */
        private $_template = 'index.tpl',
        $zones = false,
        $fetch = true
    )
    {
        if ($path) {
            // Load Package info
            $this->_module_data($path, $local_name);
            // Include module classes
            $this->_module_classes();
            if (isset($_POST['module']['status']) && is_array($_POST['module'])) {
                // Automatically handle module save requests
                $this->_info['name'] = $this->_info['name'] ?: $this->_settings['folder'];
                $this->_info['name'] = str_replace('_', ' ', $this->_info['name']);
                $this->_enumerate_raw_vars();
                foreach ($this->_rawvarsout as $key_name) {
                    $_POST['module'][$key_name] = $GLOBALS['RAW']['POST']['module'][$key_name];
                }
                if ($this->module_settings_save($_POST['module'])) {
                    $GLOBALS['main']->success_message(sprintf($GLOBALS['language']->notification['notify_module_settings'], $this->_info['name']));
                } else {
                    $GLOBALS['main']->error_message(sprintf($GLOBALS['language']->notification['error_module_settings'], $this->_info['name']));
                }
                // Install hooks if required
                if ($_POST['module']['status']) {
                    $GLOBALS['hooks']->install($this->_module_name);
                } else {
                    $GLOBALS['hooks']->uninstall($this->_module_name);
                }
                // Reload package data after save
                $this->_module_data($path, $local_name);
            }
            // Add default tab
            $GLOBALS['main']->add_tab_control($GLOBALS['language']->common['general'], $_GET['module']);
            $GLOBALS['smarty']->assign('GENERAL_TAB_ID', $_GET['module']);
            // Include module language strings - use Language class
            $GLOBALS['language']->load_definitions($this->_module_name, $this->_path . '/language', 'module.definitions.xml');
            // Load other lang either customized ones
            $GLOBALS['language']->load_language_xml($this->_module_name, '', $this->_path . '/language');
            // Enable this class as an ACP interface
            if ($this->_template) {
                $GLOBALS['gui']->change_template_dir($this->_path . '/skin');
                $module_lang_node = strtolower($this->_module_name);
                $lang = $GLOBALS['language']->get_strings($module_lang_node);
                $GLOBALS['smarty']->assign('TITLE', $this->module_fetch_logo($this->_info['type'], $this->_module_name, $lang['module_title'] ?? str_replace('_', ' ', $this->_module_name)));
                // Get tax types for modules drop down box
                if (($this->_taxes = $GLOBALS['db']->select('CubeCart_tax_class', ['id', 'tax_name'], false, ['tax_name' => 'ASC'])) !== false) {
                    $inherited_tax[] = ['id' => 999999, 'tax_name' => $GLOBALS['language']->common['inherit']];
                    $this->_taxes = array_merge($this->_taxes, $inherited_tax);
                    foreach ($this->_taxes as $tax) {
                        $tax['selected'] = isset($this->_settings['tax']) && $this->_settings['tax'] == $tax['id'] ? "selected='selected'" : '';
                        $taxes[] = $tax;
                    }
                    $GLOBALS['smarty']->assign('TAXES', $taxes);
                }
                // Assign settings
                if (!empty($this->_settings)) {
                    $GLOBALS['debug']->debug_tail($this->_settings, $this->_module_name . ': settings');
                    if ($this->_info['type'] == 'gateway') {
                        $this->_settings['processURL'] = $this->communicate_url('process');
                        $this->_settings['callURL'] = $this->communicate_url('call');
                        $this->_settings['fromURL'] = $this->communicate_url('from');
                    }
                    // Allow for 3d arrays, key is subsistuted after MODULE_ in upper case
                    foreach ($this->_settings as $key => $value) {
                        if (is_array($value)) {
                            $GLOBALS['smarty']->assign('MODULE_' . strtoupper((string) $key), $value);
                        } else {
                            $basesettings[$key] = $value;
                        }
                    }
                    $GLOBALS['smarty']->assign('MODULE', $basesettings);
                    // Assign checked & selects
                    if (is_array($this->_settings)) {
                        $filter_result = array_filter($this->_settings, is_scalar(...));
                        // removes all NULLs
                        foreach ($filter_result as $setting => $value) {
                            $value = str_replace(['.', '-'], '_', $value);
                            $GLOBALS['smarty']->assign('SELECT_' . $setting . '_' . $value, 'selected="selected"');
                            $GLOBALS['smarty']->assign('CHECKED_' . $setting . '_' . $value, 'checked="checked"');
                        }
                    }
                }
                // Assign config settings regardless
                $GLOBALS['smarty']->assign('CONFIG', $GLOBALS['config']->get('config'));
                // Zone selector + packaging
                if ($zones) {
                    $this->_module_zones();
                    if (isset($_GET['type']) && $_GET['type'] === 'shipping') {
                        $this->_module_packaging();
                    }
                    $GLOBALS['gui']->change_template_dir($this->_path . '/skin');
                }
                $GLOBALS['language']->set_template();
                if ($fetch) {
                    $this->fetch();
                }
            }
        }
    }
    //=====[ Public ]=======================================
    /**
     * Get a module value
     *
     * @return string
     */
    public function __get(string $key): mixed
    {
        return array_key_exists($key, $this->_settings) ? $this->_settings[$key] : false;
    }
    /**
     * Assign data to the template
     *
     * @param string $name
     * @param string $value
     */
    public function assign_to_template($name, $value = null): bool
    {
        if (is_array($name) && !empty($name)) {
            foreach ($name as $key => $value) {
                $this->_template_data[$key] = $value;
            }
            return true;
        }
        if (!empty($name) && !is_null($value)) {
            $this->_template_data[$name] = $value;
            return true;
        }
        return false;
    }
    /**
     * Generate URL
     *
     * @param string $method
     */
    public function communicate_url($method = 'process'): string
    {
        // SSL is preferred
        if ($method == 'from') {
            return $GLOBALS['storeURL'] . '/index.php?_a=gateway';
        }
        return $GLOBALS['storeURL'] . '/index.php?_g=rm&type=' . $this->_info['type'] . '&cmd=' . $method . '&module=' . $this->_module_name;
    }
    /**
     * Display module content
     *
     * @param bool $return
     * @return string
     */
    public function display($return = true)
    {
        if ($return) {
            return $this->_content;
        }
        echo $this->_content;
    }
    /**
     * Send template date to the screen
     */
    public function fetch()
    {
        if (!$GLOBALS['smarty']->template_exists($this->_template)) {
            return false;
        }
        foreach ($this->_template_data as $key => $value) {
            $GLOBALS['smarty']->assign($key, $value);
        }
        $this->_content = $GLOBALS['smarty']->fetch($this->_template);
        $GLOBALS['gui']->change_template_dir();
    }
    /**
     * Get module logo
     *
     * @param string $module_title
     * @return string
     */
    public function module_fetch_logo(string $type, string $name, $module_title = '')
    {
        $images = glob(CC_ROOT_DIR . '/modules/' . $type . '/' . $name . '/' . 'admin/logo.{gif,jpg,png,svg}', GLOB_BRACE);
        // $name is the module folder name, $module_title is the title set in the module lang file which is preferable
        if (is_array($images) && isset($images[0])) {
            $title = empty($module_title) ? $name : $module_title;
            return '<img src="modules/' . $type . '/' . $name . '/admin/' . basename($images[0]) . '" alt="' . $title . '" title="' . $title . '" width="114" />';
        }
        // $name is the module folder name, $module_title is the title set in the module lang file which is preferable
        if (!empty($module_title)) {
            return $module_title;
        }
        return str_replace('_', ' ', $name);
    }
    /**
     * Get module logo
     *
     * @param string $label
     * @return serialized string/empty
     */
    public function module_fetch_zones($label): string
    {
        if (!isset($_POST[$label]) || !is_array($_POST[$label])) {
            return '';
        }
        foreach ($_POST[$label] as $zone) {
            if (!empty($zone)) {
                $zones[$zone] = $zone;
            }
        }
        return isset($zones) ? serialize($zones) : '';
    }
    /**
     * Get module language strings
     *
     * @return array of strings
     */
    public function module_language()
    {
        return $this->_strings;
    }
    /**
     * Get module name
     *
     * @param string $module_name
     * @return string
     */
    public static function module_name(&$module_name): string|array|null
    {
        $module_name = preg_replace('#[^\w\-]#iU', '_', (string) $module_name);
        return $module_name;
    }
    /**
     * Save module settings
     *
     * @param array $settings
     * @return bool
     */
    public function module_settings_save($settings)
    {
        if (!empty($settings) && is_array($settings)) {
            $updated = false;
            $settings['countries'] = $this->module_fetch_zones('zones');
            $settings['disabled_countries'] = $this->module_fetch_zones('disabled_zones');
            // Save packaging boxes to global config (shared across all shipping modules)
            if (isset($_POST['packaging_boxes'])) {
                $boxes = [];
                foreach ((array) $_POST['packaging_boxes'] as $box) {
                    if (!empty($box['name'])) {
                        $boxes[] = ['name' => trim((string) $box['name']), 'l' => round((float) $box['l'], 4), 'w' => round((float) $box['w'], 4), 'h' => round((float) $box['h'], 4)];
                    }
                }
                $GLOBALS['config']->set('config', 'packaging_boxes', $boxes);
            }
            $data = ['status' => $settings['status'], 'position' => isset($settings['position']) && $settings['position'] > 0 ? $settings['position'] : 0];
            if (isset($settings['default'])) {
                $data['default'] = $settings['default'];
            }
            if ($GLOBALS['config']->set($this->_local_name, '', $settings)) {
                $updated = true;
            }
            if (isset($settings['default']) && $settings['default']) {
                // If this is to be set as default then the others need to be unset
                if ($GLOBALS['db']->update('CubeCart_modules', ['default' => 0], ['module' => $this->_info['type']])) {
                    $updated = true;
                }
            }
            // Delete to prevent potential duplicate nightmare
            $GLOBALS['db']->delete('CubeCart_modules', ['module' => $this->_info['type'], 'folder' => $this->_local_name]);
            $data['folder'] = $this->_local_name;
            $data['module'] = $this->_info['type'];
            if ($GLOBALS['db']->insert('CubeCart_modules', $data)) {
                return true;
            }
            return $updated;
        }
        return false;
    }
    //=====[ Private ]=======================================
    /**
     * Allow specified raw POST variables
     */
    private function _enumerate_raw_vars(): void
    {
        if (file_exists($this->_path . '/' . $this->_package_xml)) {
            try {
                $xml = new Simple_Xml_Element(file_get_contents($this->_path . '/' . $this->_package_xml, true));
                ## Parse and handle XML data
                foreach ((array) $xml->rawvars->var as $value) {
                    $this->_rawvarsout[] = (string) $value;
                }
            } catch (Exception $e) {
                trigger_error($e->get_message());
            }
        }
    }
    /**
     * Load module classes
     */
    private function _module_classes(): bool
    {
        // Include all classes for the module
        if (is_dir($this->_path . '/' . 'classes')) {
            foreach (glob($this->_path . '/' . 'classes' . DIRECTORY_SEPARATOR . '*.inc.php', GLOB_NOSORT) as $include) {
                if (!is_dir($include)) {
                    require $include;
                }
            }
            return true;
        }
        return false;
    }
    /**
     * Get module data
     *
     * @param string $path
     * @param string $local_name
     */
    private function _module_data($path = false, $local_name = false)
    {
        // Set Module Path
        if ($path) {
            $drop = [CC_DS . 'admin', CC_DS . 'classes', CC_DS . 'skin', CC_DS . 'language'];
            $this->_path = CC_ROOT_DIR . str_replace($drop, '', dirname(str_replace(CC_ROOT_DIR, '', $path)));
            // Drop trailing slashes
            if (str_ends_with($this->_path, '/')) {
                $this->_path = substr($this->_path, 0, -1);
            }
        }
        // Load package configuration data
        if (file_exists($this->_path . '/' . $this->_package_xml)) {
            try {
                $xml = new Simple_Xml_Element($this->_path . '/' . $this->_package_xml, LIBXML_NOCDATA, true);
                if (isset($xml->info)) {
                    $config_array = json_decode(json_encode($xml->info), true);
                    ## Parse and handle XML data
                    if (is_array($config_array)) {
                        foreach ($config_array as $key => $value) {
                            $this->_info[$key] = (string) $value;
                        }
                    }
                }
            } catch (Exception $e) {
                trigger_error($e->get_message());
                return false;
            }
            //$this->_module_name = (isset($this->_info['folder']) && !empty($this->_info['folder'])) ? $this->_info['folder'] : str_replace(' ', '_', $this->_info['name']);
        } elseif (file_exists($this->_path . '/' . $this->_package_file)) {
            $this->_info = unserialize(file_get_contents($this->_path . '/' . $this->_package_file, true));
            //$this->_module_name = str_replace(' ', '_', $this->_info['name']);
        } else {
            $path_folders = explode('/', $this->_path);
            $no_folders = count($path_folders);
            $this->_info['type'] = $path_folders[$no_folders - 2];
            //$this->_module_name = $pathFolders[($noFolders-1)];
        }
        $this->_module_name = str_replace(' ', '_', $local_name);
        $this->_local_name = $local_name ?: $this->_module_name;
        // Load module configuration
        if (!empty($this->_module_name)) {
            $config = $GLOBALS['config']->get($this->_local_name);
            $module = $GLOBALS['db']->select('CubeCart_modules', false, ['folder' => $this->_module_name]);
            //unset($config['status'], $config['default']);
            $this->_settings = $module ? array_merge($module[0], $config) : $config;
        }
    }
    /**
     * Load packaging boxes tab (global, shared across all shipping modules)
     */
    private function _module_packaging(): void
    {
        $boxes = $GLOBALS['config']->get('config', 'packaging_boxes');
        $boxes = is_array($boxes) ? $boxes : [];
        $wunit = $GLOBALS['config']->get('config', 'product_weight_unit');
        $dim_unit = $wunit === 'Lb' ? 'in' : 'cm';
        $GLOBALS['smarty']->assign('PACKAGING_BOXES', $boxes);
        $GLOBALS['smarty']->assign('PACKAGING_DIM_UNIT', $dim_unit);
        $GLOBALS['main']->add_tab_control($GLOBALS['language']->settings['packaging_tab'], 'packaging-boxes', null, null, count($boxes), '', 999999);
        $GLOBALS['gui']->change_template_dir();
        $GLOBALS['smarty']->assign('LANG', $GLOBALS['lang']);
        $packaging_html = $GLOBALS['smarty']->fetch('templates/modules.packaging.php');
        // Append to MODULE_ZONES (same mechanism as zone tabs)
        $existing = $GLOBALS['smarty']->get_template_vars('MODULE_ZONES');
        $GLOBALS['smarty']->assign('MODULE_ZONES', $existing . $packaging_html);
    }
    /**
     * Load module zones
     */
    private function _module_zones(): void
    {
        if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', ['numcode', 'name', 'status'], 'status > 0', ['name' => 'ASC'])) !== false) {
            $enabled_countries = [];
            $disabled_countries = [];
            $enabled = !empty($this->_settings['countries']) ? unserialize($this->_settings['countries']) : false;
            foreach ($countries as $country) {
                $options[$country['numcode']] = $country;
                $all_countries[] = $country;
            }
            $GLOBALS['smarty']->assign('ALL_COUNTRIES', $all_countries);
            if (is_array($enabled)) {
                sort($enabled);
                foreach ($enabled as $country) {
                    if (isset($options[$country]) && !empty($options[$country])) {
                        $enabled_countries[] = $options[$country];
                    }
                }
                $GLOBALS['smarty']->assign('ENABLED_COUNTRIES', $enabled_countries);
            }
            $GLOBALS['main']->add_tab_control($GLOBALS['language']->settings['allowed_zones'], 'zone-list', null, null, count($enabled_countries), '', 999999);
            $GLOBALS['gui']->change_template_dir();
            $GLOBALS['smarty']->assign('LANG', $GLOBALS['lang']);
            $zone_tabs = $GLOBALS['smarty']->fetch('templates/modules.zones.php');
            $disabled = !empty($this->_settings['disabled_countries']) ? unserialize($this->_settings['disabled_countries']) : false;
            if (is_array($disabled)) {
                sort($disabled);
                foreach ($disabled as $country) {
                    if (isset($options[$country]) && !empty($options[$country])) {
                        $disabled_countries[] = $options[$country];
                    }
                }
                $GLOBALS['smarty']->assign('DISABLED_COUNTRIES', $disabled_countries);
            }
            $GLOBALS['main']->add_tab_control($GLOBALS['language']->settings['disabled_zones'], 'disabled-zone-list', null, null, count($disabled_countries), '', 999999);
            $zone_tabs .= $GLOBALS['smarty']->fetch('templates/modules.zones-disabled.php');
            $GLOBALS['smarty']->assign('MODULE_ZONES', $zone_tabs);
        }
    }
}