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
 * ACP controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class ACP
{
    /**
     * Hide navigation on the admin screen
     */
    private bool $_hide_navigation = false;
    /**
     * Navigation
     */
    private array $_navigation = [];
    /**
     * Tabs
     */
    private array $_tabs = [];
    /**
     * Tab key prefix
     */
    private string $_tab_key_prefix = 'k_';
    /**
     * Tabs Priority
     */
    private int $_tab_priority = 0;
    /**
     * Wiki namespace
     */
    private string $_wiki_namespace = 'ACP';
    /**
     * Wiki page
     */
    private ?string $_wiki_page = null;
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;
    ##############################################
    final private function __construct()
    {
    }
    /**
     * Setup the instance (singleton)
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
     * Add a admin navigation item
     *
     * @param string $group
     * @param array $array (name => url)
     */
    public function add_nav_item($group, $array): void
    {
        if (!empty($array)) {
            foreach ($array as $name => $url) {
                if (is_array($url)) {
                    $this->_navigation[$group][] = ['name' => strip_tags((string) $name), 'url' => $url['address'], 'target' => isset($url['target']) && !empty($url['target']) ? $url['target'] : '_self', 'id' => isset($url['id']) && !empty($url['id']) ? $url['id'] : '', 'icon' => isset($url['icon']) && !empty($url['icon']) ? $url['icon'] : ''];
                } else {
                    $this->_navigation[$group][] = ['name' => strip_tags((string) $name), 'url' => $url, 'target' => '_self', 'id' => ''];
                }
            }
        }
    }
    /**
     * Add a tab control
     *
     * @param string $name
     * @param string $target
     * @param string $url
     * @param string $accesskey
     * @param string $notify_count
     */
    public function add_tab_control($name, $target = '', $url = null, $accesskey = null, $notify_count = false, $a_target = '_self', $priority = null, $onclick = ''): bool
    {
        if (!empty(settype($name, 'string'))) {
            $url = !empty($url) && is_array($url) ? current_page(null, $url) : $url;
            $url = is_null($url) ? '' : preg_replace('/(#.*)$/i', '', $url);
            $priority = $this->_set_tab_priority($priority);
            $this->_tabs[$this->_tab_key_prefix . $priority] = ['name' => $name, 'target' => $target, 'url' => $url, 'accesskey' => $accesskey, 'notify' => number_format((float) $notify_count), 'a_target' => $a_target, 'onclick' => $onclick];
            return true;
        }
        return false;
    }
    /**
     * Log admin message
     *
     * @param string $message
     */
    public function admin_log($message): void
    {
        $item_id = null;
        $item_type = null;
        if (isset($_REQUEST['order_id'])) {
            $item_id = $_REQUEST['order_id'];
            $item_type = 'oid';
        }
        if (isset($_REQUEST['product_id'])) {
            $item_id = $_REQUEST['product_id'];
            $item_type = 'prod';
        }
        if (isset($_REQUEST['cat_id'])) {
            $item_id = $_REQUEST['cat_id'];
            $item_type = 'cat';
        }
        if (isset($_REQUEST['doc_id'])) {
            $item_id = $_REQUEST['doc_id'];
            $item_type = 'doc';
        }
        if (!empty($message)) {
            $record = ['admin_id' => Admin::get_instance()->get_id(), 'ip_address' => get_ip_address(), 'time' => time(), 'description' => $message, 'item_id' => $item_id, 'item_type' => $item_type];
            $log_days = $GLOBALS['config']->get('config', 'r_admin_activity');
            if (ctype_digit((string) $log_days) && $log_days > 0) {
                $GLOBALS['db']->insert('CubeCart_admin_log', $record);
                if (execution_chance(10)) {
                    // 10% probability
                    $GLOBALS['db']->delete('CubeCart_admin_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ' . $log_days . ' DAY))', 500);
                }
            } elseif (empty($log_days) || !$log_days) {
                $GLOBALS['db']->insert('CubeCart_admin_log', $record);
            }
        }
    }
    /**
     * Get category path
     *
     * @param int $cat_id
     * @param int $i
     * @return data/false
     */
    public function get_category_path($cat_id, $i = 0): array|false
    {
        // get the path for a single category
        if (is_int($cat_id)) {
            if (($parent = $GLOBALS['db']->select('CubeCart_category', ['cat_id', 'cat_parent_id', 'cat_name'], ['cat_id' => $cat_id])) !== false) {
                $data[$i] = $parent[0];
                if ((int) $parent[0]['cat_parent_id'] != 0) {
                    ++$i;
                    $data = array_merge($data, $this->get_category_path($parent[0]['cat_parent_id'], $i));
                }
                sort($data);
                return $data;
            }
        }
        return false;
    }
    /**
     * Hide admin navigation
     *
     * @param bool $status
     */
    public function hide_navigation($status = false): void
    {
        $this->_hide_navigation = (bool) $status;
    }
    /**
     * Import admin node
     *
     * @param string $node
     */
    public function import_node(string $request, $node = false): string
    {
        $base = CC_ROOT_DIR . '/' . $GLOBALS['config']->get('config', 'adminFolder') . '/' . 'sources' . '/';
        $node = !empty($node) ? $node : 'index';
        $source = implode('.', [$request, $node, 'inc.php']);
        if (file_exists($base . $source)) {
            return $base . $source;
        }
        if (!is_dir($base . $request) && file_exists($base . $request . 'inc.php')) {
            return CC_ROOT_DIR . '/' . $GLOBALS['config']->get('config', 'adminFolder') . '/' . 'sources/' . $request . '.inc.php';
        }
        return CC_ROOT_DIR . '/' . $GLOBALS['config']->get('config', 'adminFolder') . '/' . 'sources/' . $request . '/' . $node . '.inc.php';
    }
    /**
     * Amount of items to list per page
     *
     * @param string $list_name
     * @param int $requested_amount
     * @param int $default_amount
     */
    public function items_per_page($list_name, $requested_amount = 0, $default_amount = 25): int
    {
        $cookie_name = 'cc_rs_limit';
        if (isset($_COOKIE[$cookie_name])) {
            $rs_limit = json_decode(html_entity_decode((string) $_COOKIE[$cookie_name], ENT_QUOTES), true);
            if (!is_array($rs_limit)) {
                $rs_limit = [];
            }
        } else {
            $rs_limit = [];
        }
        if ($requested_amount > 0) {
            $cookie_value = count($rs_limit) > 0 ? array_merge($rs_limit, [(string) $list_name => (int) $requested_amount]) : [(string) $list_name => (int) $requested_amount];
            $GLOBALS['session']->set_cookie($cookie_name, json_encode($cookie_value), time() + 3600 * 24 * 30);
            return (int) $requested_amount;
        }
        if (is_array($rs_limit) && isset($rs_limit[$list_name]) && $rs_limit[$list_name] > 0) {
            return (int) $rs_limit[$list_name];
        }
        return (int) $default_amount;
    }
    /**
     * Get latest release URL
     *
     * @return string
     */
    public function new_feature_redir()
    {
        $release_notes_path = CC_ROOT_DIR . '/' . $GLOBALS['config']->get('config', 'adminFolder') . '/sources/release_notes/*.inc.php';
        $list = glob($release_notes_path);
        arsort($list, SORT_NATURAL);
        foreach ($list as $filename) {
            $version = basename($filename);
            $version = rtrim($version, '.inc.php');
            return '?_g=release_notes&node=' . $version;
        }
    }
    /**
     * Get release notes
     */
    public function new_features($version, $features, $total, $notes = '', $security = []): string
    {
        $li = '';
        $release_notes_path = CC_ROOT_DIR . '/' . $GLOBALS['config']->get('config', 'adminFolder') . '/sources/release_notes/*.inc.php';
        $options = '';
        $list = glob($release_notes_path);
        arsort($list, SORT_NATURAL);
        foreach ($list as $filename) {
            $version = basename($filename);
            $version = rtrim($version, '.inc.php');
            $selected = $_GET['node'] == $version ? ' selected="selected"' : '';
            $options .= '<option value="?_g=release_notes&node=' . $version . '"' . $selected . '>' . $version . '</option>';
        }
        $switcher = '<select name="version" class="select_url">' . $options . '</select>';
        if (!empty($features)) {
            foreach ($features as $id => $feature) {
                $security_class = in_array($id, $security) ? 'security' : '';
                if (preg_match('/^GHSA-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}$/i', (string) $id)) {
                    $url = 'https://github.com/cubecart/v6/security/advisories/' . $id;
                    $security_class = 'security';
                } else {
                    $url = 'https://github.com/cubecart/v6/issues/' . $id;
                    $id = '#' . $id;
                }
                $li .= "<tr><td class=\"text-left {$security_class}\" valign=\"top\"><a href=\"{$url}\" target=\"_blank\">{$id}</a></td><td valign=\"top\">{$feature}</td></tr>";
            }
        } else {
            $li = '<tr><td  colspan="2">This is a maintenance release with no new features of any significance.</td></tr>';
        }
        return <<<END
        <div id="general" class="tab_content">
            <h3 style="clear: right;">CubeCart {$_GET['node']}</h3>
            <p>The table below shows changes in this version.</p>
            {$notes}
            <table class="new_features">
            <thead><tr><th>Issue</th><th><span>Version: {$switcher}</span>Description</th></tr></thead>
            <tbody>
            {$li}
            <tr><td colspan="2" class="text-center"><a href="https://github.com/cubecart/v6/issues?q=is%3Aclosed+milestone%3A{$_GET['node']}" target="_blank" class="button">View all {$total} closed issues for {$_GET['node']}</a> <a href="?" target="_self" class="button">Dashboard</a></td></tr>
            </tbody>
            </table>
        </div>
        END;
    }
    /**
     * Remove tab control
     *
     * @param string $name
     */
    public function remove_tab_control($name): static
    {
        if (!empty($name)) {
            foreach ($this->_tabs as $key => $tab) {
                if ($tab['name'] == $name) {
                    unset($this->_tabs[$key]);
                }
            }
        }
        return $this;
    }
    /**
     * Setup admin data
     */
    public function set_template(): void
    {
        if (Admin::get_instance()->is()) {
            $full_name = trim((string) Admin::get_instance()->get('name'));
            $names = explode(' ', $full_name);
            $GLOBALS['smarty']->assign('ADMIN_USER_FIRST_NAME', $names[0]);
            $GLOBALS['smarty']->assign('ADMIN_USER', $full_name);
            $GLOBALS['smarty']->assign('ADMIN_UID', Admin::get_instance()->get_id());
            if (Admin::get_instance()->get('tour_shown') == '0') {
                $GLOBALS['smarty']->assign('TOUR_AUTO_START', 'true');
            } else {
                $GLOBALS['smarty']->assign('TOUR_AUTO_START', 'false');
            }
        }
    }
    /**
     * Set admin error message
     * AN ALIAS TO A BADLY NAMED METHOD (setACPWarning)
     *
     * @param string $message
     */
    public function error_message($message, $show_once = false, $display = true): void
    {
        $this->set_acp_warning($message, $show_once, $display);
    }
    /**
     * Set admin success message
     * AN ALIAS TO A BADLY NAMED METHOD (setACPNotify)
     *
     * @param string $message
     */
    public function success_message($message, $log = true): void
    {
        $this->set_acp_notify($message, $log);
    }
    /**
     * Set admin success notice
     * LEFT FOR BACKWARD COMPATIBILITY
     *
     * @param string $message
     */
    public function set_acp_notify($message, $log = true): void
    {
        $GLOBALS['gui']->set_notify($message);
        // Add record to admin log
        if ($log) {
            $this->admin_log($message);
        }
    }
    /**
     * Set admin error notice
     * LEFT FOR BACKWARD COMPATIBILITY
     *
     * @param string $message
     */
    public function set_acp_warning($message, $show_once = false, $display = true): void
    {
        if (empty($message)) {
            return;
        }
        // Log message and don't show again to current staff member
        if ($show_once) {
            if (!$GLOBALS['db']->select('CubeCart_admin_error_log', 'log_id', ['message' => $message, 'admin_id' => Admin::get_instance()->get('admin_id')])) {
                $log_days = $GLOBALS['config']->get('config', 'r_admin_error');
                if (ctype_digit((string) $log_days) && $log_days > 0) {
                    $GLOBALS['db']->insert('CubeCart_admin_error_log', ['message' => $message, 'admin_id' => Admin::get_instance()->get('admin_id'), 'time' => time()]);
                    if (execution_chance(10)) {
                        // 10% probability
                        $GLOBALS['db']->delete('CubeCart_admin_error_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ' . $log_days . ' DAY))', 500);
                    }
                } elseif (empty($log_days) || !$log_days) {
                    $GLOBALS['db']->insert('CubeCart_admin_error_log', ['message' => $message, 'admin_id' => Admin::get_instance()->get('admin_id'), 'time' => time()]);
                }
                if ($display) {
                    $GLOBALS['gui']->set_error($message);
                }
            }
        } elseif ($display) {
            $GLOBALS['gui']->set_error($message);
        }
    }
    /**
     * Show help
     */
    public function show_help(): void
    {
        if (Admin::get_instance()->is()) {
            if (empty($this->_wiki_page)) {
                if (isset($_GET['_g']) && !empty($_GET['_g'])) {
                    $pages[] = $_GET['_g'];
                    if (isset($_GET['node']) && !empty($_GET['node']) && strtolower((string) $_GET['node']) != 'index') {
                        $pages[] = $_GET['node'];
                    }
                    if (isset($_GET['action']) && !empty($_GET['action'])) {
                        $pages[] = $_GET['action'];
                    }
                    $this->_wiki_page = implode(' ', $pages);
                }
            }
            $this->_wiki_page = preg_replace('#\W#', '_', ucwords($this->_wiki_page));
            $page = !empty($this->_wiki_namespace) ? ucfirst($this->_wiki_namespace) . ':' . $this->_wiki_page : $this->_wiki_page;
            // Assign and Parse
            $GLOBALS['smarty']->assign('HELP_URL', 'https://wiki.cubecart.com/' . $page . '?useskin=chick');
            $GLOBALS['smarty']->assign('STORE_STATUS', !(bool) Config::get_instance()->get('config', 'offline'));
        }
    }
    /**
     * Show admin tabs
     */
    public function show_tabs(): bool
    {
        if (Admin::get_instance()->is() && !empty($this->_tabs) && is_array($this->_tabs)) {
            ksort($this->_tabs);
            foreach ($this->_tabs as $tab) {
                $tab['name'] = ucfirst((string) $tab['name']);
                $tab['tab_id'] = empty($tab['target']) ? '' : 'tab_' . str_replace(' ', '_', $tab['target']);
                $tab['target'] = !empty($tab['target']) ? '#' . $tab['target'] : '';
                $tabs[] = $tab;
            }
            foreach ($GLOBALS['hooks']->load('admin.tabs') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('TABS', $tabs);
            return true;
        }
        return false;
    }
    /**
     * Show admin navigation
     */
    public function show_navigation(): bool
    {
        if (Admin::get_instance()->is() && is_array($this->_navigation) && !$this->_hide_navigation) {
            //Try cache first
            $admin_session_language = Admin::get_instance()->get('language');
            $GLOBALS['smarty']->assign('val_admin_lang', $admin_session_language);
            if (($navigation = $GLOBALS['cache']->read('acp.showNavigation.' . $admin_session_language)) === false) {
                $navigation = [];
                foreach ($this->_navigation as $group => $menu) {
                    $title = $group;
                    $group = str_replace(' ', '_', $group);
                    if (isset($_COOKIE['cc_nav_' . $group])) {
                        $visible = $_COOKIE['cc_nav_' . $group];
                    } else {
                        $visible = 'true';
                    }
                    $item = ['title' => $title, 'group' => $group, 'visible' => $visible];
                    foreach ($menu as $submenu) {
                        $item['members'][] = ['title' => ucwords((string) $submenu['name']), 'url' => $submenu['url'], 'target' => $submenu['target'] ?? '', 'id' => $submenu['id'] ?? '', 'icon' => $submenu['icon'] ?? ''];
                    }
                    $navigation[] = $item;
                }
                $GLOBALS['cache']->write($navigation, 'acp.showNavigation');
            }
            $GLOBALS['smarty']->assign('NAVIGATION', $navigation);
            return true;
        }
        return false;
    }
    /**
     * Setup wiki
     *
     * @param string $ns
     * @param string $page
     */
    public function wiki($ns = null, $page = null): void
    {
        $this->wiki_namespace($ns);
        $this->wiki_page($page);
    }
    /**
     * Setup wiki namespace
     *
     * @param string $ns
     */
    public function wiki_namespace($ns): void
    {
        if (!empty($ns)) {
            $this->_wiki_namespace = ucfirst($ns);
        }
    }
    /**
     * Setup wiki page
     *
     * @param string $page
     */
    public function wiki_page($page = null): void
    {
        $this->_wiki_page = !empty($page) ? ucwords($page) : 'Index';
    }
    //=====[ Private ]=======================================
    /**
     * Set tab priority
     *
     * @param null/int/float $priority
     */
    private function _set_tab_priority($priority = null)
    {
        if ($priority > 0) {
            if (isset($this->_tabs[$this->_tab_key_prefix . $priority])) {
                $priority += 0.01;
                return $this->_set_tab_priority($priority);
            }
            return $priority;
        }
        $priority = $this->_tab_priority;
        $this->_tab_priority++;
        return $priority;
    }
}