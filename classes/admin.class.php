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
 * Admin controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Admin
{
    /**
     * Admin's data
     *
     * @var array
     */
    private $_admin_data = [];
    /**
     * Logged in?
     */
    private bool $_logged_in = false;
    /**
     * Permission array
     */
    private array $_permissions = [];
    /**
     * Permissions sections
     */
    private array $_sections = [];
    /**
     * Length of validation key
     */
    private int $_validate_key_len = 32;
    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;
    ##############################################
    final private function __construct()
    {
        // Logout requests
        if (isset($_GET['_g']) && $_GET['_g'] == 'logout') {
            $redirect = isset($_GET['r']) && !empty($_GET['r']) ? $_GET['r'] : '';
            $this->logout($redirect);
        }
        // Ensure the ACP is only ever using the default currency
        if (ADMIN_CP == true) {
            $GLOBALS['session']->set('currency', $GLOBALS['config']->get('config', 'default_currency'), 'client');
        }
        // Action Auto-Handlers
        if (isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password'])) {
            // Login requests
            $this->_authenticate($_POST['username'], $_POST['password']);
        }
        // Load admin data
        $this->_load();
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
     * Get admin data element or the entire array if element is empty
     *
     * @param string $element
     * @return mixed
     */
    public function get($element)
    {
        if (!empty($element)) {
            return $this->_admin_data[$element] ?? false;
        }
        return $this->_admin_data;
    }
    /**
     * Get the admin id
     *
     * @return int
     */
    public function get_id()
    {
        return $this->_admin_data['admin_id'] ?? 0;
    }
    /**
     * Is admin user
     *
     * @param bool $force_login
     * @return bool
     */
    public function is($force_login = false)
    {
        if (!$force_login) {
            return $this->_logged_in;
        }
        if (!$this->_logged_in) {
            httpredir('?_a=login');
        }
        return true;
    }
    /**
     * Logout of admin
     */
    public function logout($redirect = ''): void
    {
        $this->_load();
        $GLOBALS['db']->update('CubeCart_admin_users', ['session_id' => ''], ['admin_id' => (int) $this->_admin_data['admin_id']]);
        $GLOBALS['session']->destroy();
        if ($redirect == 'front') {
            httpredir($GLOBALS['rootRel']);
        } else {
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
        }
    }
    /**
     * Reset password
     *
     * @param string $email
     * @param string $validation
     * @param string $password
     * @return bool
     */
    public function password_reset($email, $validation, $password)
    {
        $email = preg_replace('/[^a-z0-9.@_\-\+]/i', '', $email);
        $validation = preg_replace('/[^a-z0-9]/i', '', $validation);
        if ($GLOBALS['session']->has('recover_login') && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen((string) $validation) == $this->_validate_key_len && !empty($password['new']) && !empty($password['confirm']) && $password['new'] === $password['confirm']) {
            if (($check = $GLOBALS['db']->select('CubeCart_admin_users', ['admin_id', 'username'], ['email' => $email, 'verify' => $validation, 'status' => 1])) !== false) {
                // Remove any blocks
                $GLOBALS['db']->delete('CubeCart_blocker', ['username' => $email]);
                $salt = Password::get_instance()->create_salt();
                $record = ['salt' => $salt, 'password' => Password::get_instance()->get_salted($password['new'], $salt), 'verify' => null, 'new_password' => 1];
                $where = ['admin_id' => $check[0]['admin_id'], 'email' => $email, 'verify' => $validation];
                $GLOBALS['session']->delete('recover_login');
                if ($GLOBALS['db']->update('CubeCart_admin_users', $record, $where)) {
                    return $this->_authenticate($check[0]['username'], $password['new']);
                }
            }
        }
        return false;
    }
    /**
     * Request password
     *
     * @param string $email
     * @return bool
     */
    public function password_request($email)
    {
        $email = preg_replace('/[^a-z0-9.@_\-\+]/i', '', $email);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($check = $GLOBALS['db']->select('CubeCart_admin_users', ['admin_id', 'email', 'language', 'name'], ['email' => $email, 'status' => 1])) {
                // Generate validation key
                $validation = random_string($this->_validate_key_len);
                if ($GLOBALS['db']->update('CubeCart_admin_users', ['verify' => $validation], ['admin_id' => (int) $check[0]['admin_id']])) {
                    // Send email
                    $mailer = new Mailer();
                    $data['link'] = $GLOBALS['storeURL'] . '/' . $GLOBALS['config']->get('config', 'adminFile') . '?_g=recovery&email=' . $check[0]['email'] . '&validate=' . $validation;
                    $data['name'] = $check[0]['name'];
                    $content = $mailer->load_content('admin.password_recovery', $check[0]['language'], $data);
                    if ($content) {
                        $GLOBALS['smarty']->assign('DATA', $data);
                        $GLOBALS['session']->set('recover_login', true);
                        return $mailer->send_email($check[0]['email'], $content);
                    }
                }
            }
        }
        return false;
    }
    /**
     * Check admin permissions
     *
     * @param mixed $sections
     * @param unknown_type $level
     * @param unknown_type $halt
     */
    public function permissions($sections, $level = 4, $halt = false, $message = true): bool
    {
        // Are they a Superuser? If so, they get automatic authorization
        if ($this->super_user()) {
            return true;
        }
        // Lets update permissions to handle an array sections
        if (is_array($sections)) {
            foreach ($sections as $section) {
                $departments[] = !is_numeric($section) ? $this->_get_section_id($section) : (int) $section;
            }
        } else {
            // Get integers for section and permission level
            $departments[] = !is_numeric($sections) ? $this->_get_section_id($sections) : (int) $sections;
        }
        $level = !is_numeric($level) ? $this->_convert_permission($level) : (int) $level;
        if (is_array($departments)) {
            foreach ($departments as $section_id) {
                // Do they have permission to be here?
                if (isset($this->_permissions[$section_id])) {
                    // Check Section specific permissions
                    if ($this->_permissions[$section_id] & $level) {
                        $allowed = true;
                        continue;
                    }
                } elseif (isset($this->_permissions[0])) {
                    // Check global permissions
                    if ($this->_permissions[0] & $level) {
                        $allowed = true;
                        continue;
                    }
                }
                $allowed = false;
                break;
            }
        }
        // Are they authorized?
        if ($allowed) {
            return true;
        }
        // Unauthorized - do we redirect, or just return false?
        if ($message) {
            $GLOBALS['main']->error_message($GLOBALS['language']->notification['error_privileges']);
        }
        if ($halt) {
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile') . '?_g=401');
        }
        return false;
    }
    /**
     * Is a super user
     */
    public function super_user(): bool
    {
        return $this->_admin_data['super_user'] ? true : false;
    }
    //=====[ Private ]=======================================
    /**
     * Authenticate user as admin
     *
     * @param string $username
     * @param string $password
     */
    private function _authenticate($username, $password): bool
    {
        $username = (string) $username;
        $password = (string) $password;
        $hash_password = '';
        if (!empty($username)) {
            // Fetch salt
            if (($user = $GLOBALS['db']->select('CubeCart_admin_users', ['admin_id', 'password', 'salt', 'new_password'], ['username' => $username, 'status' => '1'], null, 1)) !== false) {
                if (empty($user[0]['salt'])) {
                    // Generate Salt
                    $salt = Password::get_instance()->create_salt();
                    //Update it to the newer MD5 so we can fix it later
                    $pass = Password::get_instance()->update_old($user[0]['password'], $salt);
                    $update = ['salt' => $salt, 'password' => $pass, 'new_password' => 0];
                    if ($GLOBALS['db']->update('CubeCart_admin_users', $update, ['admin_id' => (int) $user[0]['admin_id']])) {
                        $hash_password = $pass;
                    }
                } else if ($user[0]['new_password'] == 1) {
                    //Get the salted new password
                    $hash_password = Password::get_instance()->get_salted($password, $user[0]['salt']);
                } else {
                    //Get the salted old password
                    $hash_password = Password::get_instance()->get_salted_old($password, $user[0]['salt']);
                }
            } else {
                foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_invalid_admin') as $hook) {
                    include $hook;
                }
                $GLOBALS['gui']->set_error($GLOBALS['language']->account['error_login']);
                return false;
            }
            $result = $GLOBALS['db']->select('CubeCart_admin_users', ['admin_id', 'customer_id', 'logins', 'new_password', 'name', 'email', 'language', 'twofa_enabled', 'twofa_method', 'twofa_secret', 'ip_address', 'browser'], ['username' => $username, 'password' => $hash_password, 'status' => '1']);
            $GLOBALS['session']->blocker($username, 0, (bool) $result, Session::BLOCKER_BACKEND, $GLOBALS['config']->get('config', 'bfattempts'), $GLOBALS['config']->get('config', 'bftime'));
            if ($result) {
                if (!$GLOBALS['session']->blocked()) {
                    $update = ['blockTime' => 0, 'browser' => htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']), 'failLevel' => 0, 'ip_address' => get_ip_address(), 'verify' => '', 'lastTime' => time(), 'logins' => $result[0]['logins'] + 1];
                    if ($result[0]['new_password'] != 1) {
                        $salt = Password::get_instance()->create_salt();
                        $pass = Password::get_instance()->get_salted($password, $salt);
                        $update = array_merge($update, ['salt' => $salt, 'password' => $pass, 'new_password' => 1]);
                    }
                    $GLOBALS['db']->update('CubeCart_admin_users', $update, ['admin_id' => (int) $result[0]['admin_id']]);
                    $this->_send_new_device_notification($result[0]);
                    if (!empty($result[0]['twofa_enabled'])) {
                        // 2FA required – store pending state and redirect to challenge page (exits)
                        $this->_initiate2FA($result[0]);
                    }
                    // No 2FA – establish session directly (sets _logged_in, admin_id in session, calls _load)
                    $this->_establish_session((int) $result[0]['admin_id']);
                } else {
                    foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_valid_admin') as $hook) {
                        include $hook;
                    }
                    $minutes_blocked = ceil($GLOBALS['config']->get('config', 'bftime') / 60);
                    $GLOBALS['gui']->set_error(sprintf('Too many invalid logins have been made. Access has been blocked for %s minutes.', $minutes_blocked));
                }
            } else {
                if (!$GLOBALS['session']->blocked()) {
                    if (($user = $GLOBALS['db']->select('CubeCart_admin_users', false, ['username' => $_POST['username']])) !== false) {
                        if ($user[0]['blockTime'] > 0 && $user[0]['blockTime'] < time()) {
                            // reset fail level and time
                            $newdata['failLevel'] = 1;
                            $newdata['blockTime'] = 0;
                        } elseif ($user[0]['failLevel'] == $GLOBALS['config']->get('config', 'bfattempts') - 1) {
                            $time_ago = time() - $GLOBALS['config']->get('config', 'bftime');
                            if ($user[0]['lastTime'] < $time_ago) {
                                $newdata['failLevel'] = 1;
                                $newdata['blockTime'] = 0;
                            } else {
                                // block the account
                                $newdata['failLevel'] = $GLOBALS['config']->get('config', 'bfattempts');
                                $newdata['blockTime'] = time() + $GLOBALS['config']->get('config', 'bftime');
                            }
                        } elseif ($user[0]['blockTime'] < time()) {
                            $time_ago = time() - $GLOBALS['config']->get('config', 'bftime');
                            $newdata['failLevel'] = $user[0]['lastTime'] < $time_ago ? 1 : $user[0]['failLevel'] + 1;
                            $newdata['blockTime'] = 0;
                        } else {
                            // Display Blocked message
                            $GLOBALS['gui']->set_error(sprintf($GLOBALS['language']->account['error_login_block'], $GLOBALS['config']->get('config', 'bftime') / 60));
                        }
                        if (isset($newdata)) {
                            $newdata['lastTime'] = time();
                            $GLOBALS['db']->update('CubeCart_admin_users', $newdata, ['admin_id' => $user[0]['admin_id']]);
                        }
                    }
                    $GLOBALS['gui']->set_error($GLOBALS['language']->account['error_login']);
                } else {
                    $minutes_blocked = ceil($GLOBALS['config']->get('config', 'bftime') / 60);
                    $GLOBALS['gui']->set_error(sprintf('Too many invalid logins have been made. Access has been blocked for %s minutes.', $minutes_blocked));
                }
                foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_valid_admin') as $hook) {
                    include $hook;
                }
            }
            if (!$GLOBALS['session']->blocked()) {
                $redir = '';
                if (isset($_GET['redir']) && !empty($_GET['redir'])) {
                    $redir = $_GET['redir'];
                } elseif (isset($_POST['redir']) && !empty($_POST['redir'])) {
                    $redir = $_POST['redir'];
                } elseif ($GLOBALS['session']->has('redir')) {
                    $redir = $GLOBALS['session']->get('redir');
                } elseif ($GLOBALS['session']->has('back')) {
                    $redir = $GLOBALS['session']->get('back');
                }
                if (!empty($redir)) {
                    // Prevent phishing attacks, or anything untoward, unless it's redirecting back to this store
                    if (!$GLOBALS['ssl']->valid_redirect($redir)) {
                        trigger_error(sprintf("Possible Phishing attack - Redirection to '%s' is not allowed. Please check the value of 'Store URL' in the SSL section of your store settings.", $redir));
                        $redir = '';
                        if ($GLOBALS['session']->has('back') && $redir == $GLOBALS['session']->get('back')) {
                            $GLOBALS['session']->delete('back');
                        }
                        if ($GLOBALS['session']->has('redir') && $redir == $GLOBALS['session']->get('redir')) {
                            $GLOBALS['session']->delete('redir');
                        }
                    }
                }
                httpredir(isset($redir) && !empty($redir) ? $redir : $GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
            } else {
                $minutes_blocked = ceil($GLOBALS['config']->get('config', 'bftime') / 60);
                $GLOBALS['gui']->set_error(sprintf('Too many invalid logins have been made. Access has been blocked for %s minutes.', $minutes_blocked));
            }
        } else {
            $GLOBALS['gui']->set_error($GLOBALS['language']->account['error_login']);
        }
        return false;
    }
    /**
     * Convert permissions
     *
     * @param string $name
     * @return int
     */
    private function _convert_permission($name = null)
    {
        return match (strtolower((string) $name)) {
            'delete' => CC_PERM_DELETE,
            'edit', 'write' => CC_PERM_EDIT,
            'read' => CC_PERM_READ,
            default => 0,
        };
    }
    /**
     * Get the admin section id
     *
     * @param unknown_type $name
     * @return int/false
     */
    private function _get_section_id($name)
    {
        if (!empty($name)) {
            foreach ($GLOBALS['hooks']->load('class.admin.get_section_id') as $hook) {
                include $hook;
            }
            $sections = ['categories' => 3, 'customers' => 5, 'documents' => 4, 'filemanager' => 7, 'offers' => 11, 'orders' => 10, 'products' => 2, 'users' => 1, 'shipping' => 6, 'statistics' => 8, 'settings' => 9, 'reviews' => 12];
            if (isset($sections[$name])) {
                return (int) $sections[$name];
            }
            foreach ($this->_sections as $section) {
                if ($section['name'] == strtolower($name)) {
                    return $section['section_id'];
                }
            }
        }
        return false;
    }
    /**
     * Load admin data
     */
    private function _load(): bool
    {
        //Try to get the admin_id from the sessions
        $admin_id = $GLOBALS['session']->get('admin_id', 'client', 0);
        //If there is one
        if ($admin_id != 0) {
            //Try to get the admin_data from the sessions
            if ($GLOBALS['session']->has('', 'admin_data')) {
                $data = $GLOBALS['session']->get('', 'admin_data');
            }
            if (!isset($data) || empty($data) || !isset($data['admin_id'])) {
                //Load from the DB
                if (($data = $GLOBALS['db']->select('CubeCart_admin_users', false, ['admin_id' => $admin_id, 'status' => '1'], false, 1, false, false)) !== false) {
                    //Unset these for security reasons
                    unset($data[0]['password']);
                    unset($data[0]['salt']);
                    unset($data[0]['session_id']);
                    $GLOBALS['session']->set('', $data[0], 'admin_data');
                    $data = $data[0];
                    $GLOBALS['db']->update('CubeCart_sessions', ['admin_id' => $data['admin_id']], ['session_id' => $GLOBALS['session']->get_id()]);
                }
            }
            if (!empty($data)) {
                $this->_logged_in = true;
                $this->_admin_data = $data;
                $GLOBALS['session']->set('user_language', !empty($data['language']) ? $data['language'] : $GLOBALS['config']->get('config', 'default_language'), 'admin');
                // Load Permission Rules
                if (($permissions = $GLOBALS['db']->select('CubeCart_permissions', false, ['admin_id' => $this->_admin_data['admin_id']])) !== false) {
                    foreach ($permissions as $permission) {
                        $this->_permissions[$permission['section_id']] = $permission['level'];
                    }
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Magic get
     */
    public function __get(string $name): mixed
    {
        return $this->_admin_data[$name] ?? false;
    }
    /**
     * Verify a 2FA code (TOTP, email OTP, or backup code) for a pending login
     *
     * @param string $code
     * @return bool  Always redirects on success; returns false on failure
     */
    public function verify2FA($code): bool
    {
        $admin_id = (int) $GLOBALS['session']->get('twofa_pending_admin_id', 'client', 0);
        if (!$admin_id) {
            return false;
        }
        // IP binding – prevent session token theft between credential check and 2FA entry
        $pending_ip = $GLOBALS['session']->get('twofa_pending_ip', 'client');
        if ($pending_ip !== md5(get_ip_address())) {
            $this->_clear_pending2fa();
            $GLOBALS['gui']->set_error($GLOBALS['language']->account['error_twofa_session']);
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
        }
        $admin = $GLOBALS['db']->select('CubeCart_admin_users', false, ['admin_id' => $admin_id, 'status' => 1], false, 1, false, false);
        if (!$admin) {
            $this->_clear_pending2fa();
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
        }
        $admin = $admin[0];
        $code = trim((string) $code);
        $verified = false;
        // Check backup codes (8 uppercase hex chars)
        if (strlen($code) === 8 && preg_match('/^[A-F0-9]+$/i', $code)) {
            $backup_codes = json_decode($admin['twofa_backup_codes'] ?? '[]', true);
            if (is_array($backup_codes)) {
                foreach ($backup_codes as $i => $hash) {
                    if (password_verify(strtoupper($code), (string) $hash)) {
                        unset($backup_codes[$i]);
                        $GLOBALS['db']->update('CubeCart_admin_users', ['twofa_backup_codes' => json_encode(array_values($backup_codes))], ['admin_id' => $admin_id]);
                        $verified = true;
                        break;
                    }
                }
            }
        }
        if (!$verified) {
            if ($admin['twofa_method'] === 'email') {
                if (!empty($admin['twofa_otp_hash']) && (int) $admin['twofa_otp_expires'] > time()) {
                    if (password_verify($code, (string) $admin['twofa_otp_hash'])) {
                        $GLOBALS['db']->update('CubeCart_admin_users', ['twofa_otp_hash' => null, 'twofa_otp_expires' => 0], ['admin_id' => $admin_id]);
                        $verified = true;
                    }
                }
            } elseif ($admin['twofa_method'] === 'totp') {
                if (!empty($admin['twofa_secret'])) {
                    require_once CC_ROOT_DIR . CC_DS . 'classes' . CC_DS . 'totp.class.php';
                    $verified = TOTP::verify_code($admin['twofa_secret'], $code);
                }
            }
        }
        if ($verified) {
            $redir = (string) $GLOBALS['session']->get('twofa_pending_redir', 'client', '');
            $this->_clear_pending2fa();
            $this->_establish_session($admin_id);
            if (!empty($redir) && $GLOBALS['ssl']->valid_redirect($redir)) {
                httpredir($redir);
            }
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
        }
        // Failed attempt – session-based rate limiting
        $fail_count = (int) $GLOBALS['session']->get('twofa_fail_count', 'client', 0) + 1;
        $max_attempts = (int) $GLOBALS['config']->get('config', 'bfattempts') ?: 5;
        if ($fail_count >= $max_attempts) {
            $this->_clear_pending2fa();
            $minutes = ceil($GLOBALS['config']->get('config', 'bftime') / 60);
            $GLOBALS['gui']->set_error(sprintf($GLOBALS['language']->account['error_twofa_locked'], $minutes));
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
        }
        $GLOBALS['session']->set('twofa_fail_count', $fail_count, 'client');
        $GLOBALS['gui']->set_error($GLOBALS['language']->account['error_twofa_invalid']);
        return false;
    }
    /**
     * Resend the email OTP for a pending 2FA login (rate-limited to once per 60 s)
     */
    public function resend2fa_code(): bool
    {
        $admin_id = (int) $GLOBALS['session']->get('twofa_pending_admin_id', 'client', 0);
        if (!$admin_id) {
            return false;
        }
        $admin = $GLOBALS['db']->select('CubeCart_admin_users', ['admin_id', 'name', 'email', 'language', 'twofa_method', 'twofa_otp_expires'], ['admin_id' => $admin_id, 'status' => 1, 'twofa_method' => 'email'], false, 1, false, false);
        if (!$admin) {
            return false;
        }
        $admin = $admin[0];
        // Allow resend only if at least 60 seconds have elapsed since last send
        $sent_at = (int) $admin['twofa_otp_expires'] - 600;
        if (time() - $sent_at < 60) {
            $GLOBALS['gui']->set_error($GLOBALS['language']->account['error_twofa_wait']);
            return false;
        }
        $this->_send2fa_code($admin);
        $GLOBALS['gui']->set_notify($GLOBALS['language']->account['notify_twofa_resent']);
        return true;
    }
    /**
     * Establish an authenticated admin session after credentials (and 2FA) are verified
     */
    private function _establish_session(int $admin_id): void
    {
        $GLOBALS['session']->regenerate_session_id();
        $GLOBALS['db']->update('CubeCart_admin_users', ['session_id' => $GLOBALS['session']->get_id()], ['admin_id' => $admin_id]);
        $GLOBALS['session']->set('admin_id', $admin_id, 'client');
        $this->_logged_in = true;
        $this->_load();
    }
    /**
     * Initiate the 2FA challenge: store pending state, send email OTP if needed, then redirect
     *
     * @param array $admin  admin record from DB (must include admin_id, twofa_method, name, email, language)
     */
    private function _initiate2FA(array $admin): void
    {
        $GLOBALS['session']->set('twofa_pending_admin_id', (int) $admin['admin_id'], 'client');
        $GLOBALS['session']->set('twofa_pending_ip', md5(get_ip_address()), 'client');
        // Preserve any post-login redirect destination
        $redir = '';
        if (isset($_POST['redir']) && !empty($_POST['redir'])) {
            $redir = $_POST['redir'];
        } elseif (isset($_GET['redir']) && !empty($_GET['redir'])) {
            $redir = $_GET['redir'];
        }
        if (!empty($redir)) {
            $GLOBALS['session']->set('twofa_pending_redir', $redir, 'client');
        }
        if ($admin['twofa_method'] === 'email') {
            $this->_send2fa_code($admin);
        }
        httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile') . '?_g=twofa');
    }
    /**
     * Generate and email a 6-digit OTP to the admin
     *
     * @param array $admin  must include admin_id, name, email, language
     */
    private function _send2fa_code(array $admin): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $expires = time() + 600;
        $GLOBALS['db']->update('CubeCart_admin_users', ['twofa_otp_hash' => $hash, 'twofa_otp_expires' => $expires], ['admin_id' => (int) $admin['admin_id']]);
        $mailer = new Mailer();
        $data = ['code' => $code, 'name' => $admin['name'] ?? '', 'expires' => '10 minutes'];
        $content = $mailer->load_content('admin.two_factor_code', $admin['language'], $data);
        if ($content) {
            $GLOBALS['smarty']->assign('DATA', $data);
            $mailer->send_email($admin['email'], $content);
        }
    }
    /**
     * Send notification email if admin is logging in from a new IP or device
     *
     * @param array $admin  Admin record (must include ip_address, browser, name, email, language)
     */
    private function _send_new_device_notification(array $admin): void
    {
        if ($GLOBALS['config']->get('config', 'admin_login_notify') === '0') {
            return;
        }
        $prev_ip = isset($admin['ip_address']) ? trim($admin['ip_address']) : '';
        $prev_browser = isset($admin['browser']) ? trim($admin['browser']) : '';
        // Skip first-ever login — nothing to compare against
        if (empty($prev_ip) && empty($prev_browser)) {
            return;
        }
        $current_ip = get_ip_address();
        $current_browser = htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']);
        $ip_changed = !empty($prev_ip) && $prev_ip !== $current_ip;
        $browser_changed = !empty($prev_browser) && $this->_normalize_browser($prev_browser) !== $this->_normalize_browser($current_browser);
        if (!$ip_changed && !$browser_changed) {
            return;
        }
        $mailer = new Mailer();
        $data = ['name' => $admin['name'] ?? '', 'new_ip' => $current_ip, 'previous_ip' => $prev_ip, 'new_browser' => htmlspecialchars_decode($current_browser), 'previous_browser' => htmlspecialchars_decode($prev_browser), 'login_time' => date('Y-m-d H:i:s T'), 'ip_changed' => $ip_changed, 'browser_changed' => $browser_changed];
        $content = $mailer->load_content('admin.new_device_login', $admin['language'], $data);
        if ($content) {
            $GLOBALS['smarty']->assign('DATA', $data);
            $mailer->send_email($admin['email'], $content);
        }
    }
    /**
     * Normalize a user agent string by stripping version numbers
     * so that browser auto-updates don't trigger false positives
     *
     * @return string
     */
    private function _normalize_browser(string $ua): ?string
    {
        return preg_replace('/\/[\d]+[\d.]*/', '/', $ua);
    }
    /**
     * Clear all pending 2FA session state
     */
    private function _clear_pending2fa(): void
    {
        $GLOBALS['session']->delete('twofa_pending_admin_id', 'client');
        $GLOBALS['session']->delete('twofa_pending_ip', 'client');
        $GLOBALS['session']->delete('twofa_pending_redir', 'client');
        $GLOBALS['session']->delete('twofa_fail_count', 'client');
    }
}