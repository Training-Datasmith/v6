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
 * Newsletter management
 *
 * @author Martin Purcell
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Newsletter
{
    private readonly \Mailer $_mailer;
    private array $_validated_domain = [];
    public $_newsletter_id;
    protected static $_instance;
    ##############################################
    public function __construct()
    {
        $this->_mailer = new Mailer();
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
    /**
     * Clean up mailing list
     *
     * @return array('deleted' => int, 'unsubscribed' => int);
     */
    public function clean_list(): array
    {
        $rows = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', ['subscriber_id', 'email']);
        $return = ['deleted' => 0, 'unsubscribed' => 0];
        if ($rows) {
            foreach ($rows as $row) {
                if ($this->validate_email($row['email']) == 2) {
                    if ($GLOBALS['db']->delete('CubeCart_newsletter_subscriber', ['subscriber_id' => $row['subscriber_id']])) {
                        $this->_subscriber_log($row['email'], 'Invalid email address deleted from mailing list.');
                        $return['deleted']++;
                    }
                } elseif ($this->validate_email($row['email']) == 0) {
                    if ($GLOBALS['db']->update('CubeCart_newsletter_subscriber', ['status' => 0], ['subscriber_id' => $row['subscriber_id']])) {
                        $this->_subscriber_log($row['email'], 'No valid MX record found. Status set to disabled.');
                        $return['unsubscribed']++;
                    }
                }
            }
        }
        return $return;
    }
    //=====[ Public ]=======================================
    /**
     * Delete newsletter
     *
     * @param int $newsletter_id
     */
    public function delete_newsletter($newsletter_id = false): bool
    {
        if ($newsletter_id && is_numeric($newsletter_id)) {
            $GLOBALS['db']->delete('CubeCart_newsletter', ['newsletter_id' => (int) $newsletter_id]);
            return true;
        }
        return false;
    }
    /**
     * Empty newsletter
     *
     * @return bool
     */
    public function empty_list()
    {
        return $GLOBALS['db']->misc('TRUNCATE TABLE `' . $GLOBALS['config']->get('config', 'dbprefix') . 'CubeCart_newsletter_subscriber`;');
    }
    /**
     * Generate validaton key for email verification
     *
     * @param string $key
     */
    private function generate_validation($email): string
    {
        // Generate a validation key for the specified email address
        $string = sprintf('%s@%s', crypt((string) $email, (string) time()), date('U.u'));
        return md5($string);
    }
    /**
     * Save newsletter
     *
     * @param array $newsletter
     * @return bool
     */
    public function save_newsletter($newsletter = false)
    {
        $result = false;
        if (!empty($newsletter) && is_array($newsletter)) {
            if (!empty($newsletter['newsletter_id']) && is_numeric($newsletter['newsletter_id'])) {
                $result = $GLOBALS['db']->update('CubeCart_newsletter', $newsletter, ['newsletter_id' => $newsletter['newsletter_id']]);
                $this->_newsletter_id = $newsletter['newsletter_id'];
            } else {
                $this->_newsletter_id = $result = $GLOBALS['db']->insert('CubeCart_newsletter', $newsletter);
            }
        }
        return $result;
    }
    /**
     * Send newsletter
     *
     * @param int $newsletter_id
     * @param int $cycle
     * @param bool $test
     * @return bool
     */
    public function send_newsletter($newsletter_id = false, $cycle = 1, $test = false): array|bool
    {
        // Load newsletter from database, and send
        if ($newsletter_id && is_numeric($newsletter_id)) {
            if (($contents = $GLOBALS['db']->select('CubeCart_newsletter', false, ['newsletter_id' => (int) $newsletter_id])) !== false) {
                $content = $contents[0];
                if (!empty($content['sender_name'])) {
                    $this->_mailer->from_name = $content['sender_name'];
                }
                if (!empty($content['sender_email'])) {
                    $this->_mailer->From = $content['sender_email'];
                }
                if ($test) {
                    // Send test email only
                    if (filter_var($test, FILTER_VALIDATE_EMAIL)) {
                        $this->unsubscribe_header($test);
                        if ($this->_mailer->send_email($test, $content, $contents[0]['template_id'])) {
                            $log = sprintf($GLOBALS['language']->newsletter['test_subscriber_log'], $contents[0]['subject'], $this->_mailer->get_template_title());
                            $this->_subscriber_log($test, $log);
                        }
                        return true;
                    }
                } else {
                    ini_set('ignore_user_abort', true);
                    // Send to all subscribers
                    $limit = 20;
                    $where = ['status' => '1'];
                    if ($content['dbl_opt'] == 1) {
                        $where['dbl_opt'] = 1;
                    }
                    $total = (int) $GLOBALS['db']->count('CubeCart_newsletter_subscriber', 'status', $where);
                    if ($total == 0 && $cycle == 1) {
                        $GLOBALS['gui']->set_error($GLOBALS['language']->newsletter['no_subscribers']);
                    }
                    if (($subscribers = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', ['email'], $where, false, $limit, $cycle)) !== false) {
                        foreach ($subscribers as $subscriber) {
                            if (filter_var($subscriber['email'], FILTER_VALIDATE_EMAIL)) {
                                $content = ['subject' => $content['subject'], 'content_html' => $content['content_html']];
                                $this->unsubscribe_header($subscriber['email']);
                                if ($this->_mailer->send_email($subscriber['email'], $content, $contents[0]['template_id'])) {
                                    $log = sprintf($GLOBALS['language']->newsletter['subscriber_log'], $contents[0]['subject'], $this->_mailer->get_template_title());
                                    $this->_subscriber_log($subscriber['email'], $log);
                                }
                            } else {
                                // Flag for deletion
                                $GLOBALS['db']->update('CubeCart_newsletter_subscriber', ['status' => '9'], ['email' => $subscriber['email']]);
                            }
                        }
                        $sent_to = $limit * $cycle;
                        if ($total > $sent_to) {
                            return ['count' => $sent_to, 'total' => $total, 'percent' => $sent_to / $total * 100];
                        }
                        // Delete flagged subscribers
                        $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', ['status' => '9']);
                        // Update newsletter record
                        $GLOBALS['db']->update('CubeCart_newsletter', ['date_sent' => 'CURRENT_TIMESTAMP', 'status' => 1], ['newsletter_id' => (int) $newsletter_id]);
                        return true;
                    }
                    return false;
                }
            }
        }
        return false;
    }
    /**
     * Subscribe to newsletter
     *
     * @param string $email
     */
    public function subscribe($email = false, $customer_id = null): bool
    {
        if ($GLOBALS['config']->get('config', 'newsletter_status') === '0') {
            return false;
        }
        $checkout = in_array($_GET['_a'], ['confirm', 'checkout', 'basket']) ? true : false;
        if ($checkout && $GLOBALS['config']->get('config', 'dbl_opt') == '1' && $GLOBALS['session']->has('dbl_opted') && $GLOBALS['session']->get('dbl_opted') == $email) {
            return false;
        }
        $skin_data = GUI::get_instance()->get_skin_data();
        $error = false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $GLOBALS['gui']->set_error(sprintf($GLOBALS['language']->newsletter['email_invalid'], $email));
            $error = true;
        } else {
            // Avoid recursion: Newsletter::subscribe() can be called during User construction,
            // so DO NOT call User::getInstance() here.
            $logged_in = !empty($GLOBALS['session']->session_data['customer_id']) && (int) $GLOBALS['session']->session_data['customer_id'] > 0;
            if (!$logged_in && !empty($skin_data['info']['newsletter_recaptcha']) && GUI::get_instance()->recaptcha_required() && $GLOBALS['session']->get('error', 'recaptcha')) {
                $GLOBALS['gui']->set_error($GLOBALS['session']->get('error', 'recaptcha'));
                $error = true;
            }
        }
        if ($error) {
            httpredir(current_page());
        } else {
            $email = strtolower($email);
            $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', ['email' => $email]);
            $record = ['status' => true, 'email' => $email, 'customer_id' => $customer_id, 'validation' => $this->generate_validation($email), 'ip_address' => get_ip_address(), 'date' => date('c')];
            $GLOBALS['db']->insert('CubeCart_newsletter_subscriber', $record);
            if ((bool) $GLOBALS['config']->get('config', 'dbl_opt')) {
                $mailer = new Mailer();
                if (($content = $mailer->load_content('newsletter.verify_email', $GLOBALS['language']->current())) !== false) {
                    $GLOBALS['smarty']->assign('DATA', ['email' => $email, 'link' => CC_STORE_URL . '?_a=newsletter&do=' . $record['validation']]);
                    $mailer->send_email($email, $content);
                    $GLOBALS['session']->set('dbl_opted', $email);
                }
                $this->_subscriber_log($email, 'Subscribed pending double opt-in verification');
                if (!$checkout) {
                    $GLOBALS['gui']->set_notify($GLOBALS['language']->newsletter['notify_subscribed_opt_in']);
                }
            } else {
                $this->_subscriber_log($email, 'Subscribed without double opt-in.');
                if (!$checkout) {
                    $GLOBALS['gui']->set_notify($GLOBALS['language']->newsletter['notify_subscribed']);
                }
            }
            foreach ($GLOBALS['hooks']->load('class.newsletter.subscribe') as $hook) {
                include $hook;
            }
            return true;
        }
        return false;
    }
    /**
     * Unsubscribe from newsletter
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribe($email = false, $customer_id = false)
    {
        if ($GLOBALS['config']->get('config', 'newsletter_status') === '0') {
            return false;
        }
        // Unsubscribe the user
        $removed = false;
        $remove_token = null;
        if (ctype_digit((string) $customer_id) && $customer_id > 0) {
            $removed = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', ['customer_id' => $customer_id]);
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (defined('ADMIN_CP') && ADMIN_CP === true || isset($_GET['rt']) && !empty($_GET['rt'])) {
                $where = isset($_GET['rt']) ? ['remove_token' => $_GET['rt']] : ['email' => $email];
                $removed = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', $where);
            } else {
                $remove_token = md5(uniqid((string) time(), true));
                $remove_possible = $GLOBALS['db']->update('CubeCart_newsletter_subscriber', ['remove_token' => $remove_token], ['email' => $email]);
            }
            foreach ($GLOBALS['hooks']->load('class.newsletter.unsubscribe') as $hook) {
                include $hook;
            }
        }
        if (!empty($remove_token)) {
            $this->_subscriber_log($email, 'Removal requested. Pending confirmation.');
            $mailer = new Mailer();
            if ($remove_possible && ($content = $mailer->load_content('newsletter.remove_request', $GLOBALS['language']->current())) !== false) {
                $GLOBALS['smarty']->assign('DATA', ['email' => $email, 'link' => CC_STORE_URL . '/index.php?_a=unsubscribe&unsubscribe=' . urlencode($email) . '&rt=' . $remove_token]);
                $mailer->send_email($email, $content);
                $this->_subscriber_log($email, 'Removal requested email sent.');
            }
            $GLOBALS['gui']->set_notify($GLOBALS['language']->newsletter['notify_remove_request']);
            return true;
        }
        if ($removed) {
            $this->_subscriber_log($email, 'Removed from mailing list');
            $GLOBALS['gui']->set_notify($GLOBALS['language']->newsletter['notify_unsubscribed']);
        } else {
            $GLOBALS['gui']->set_error($GLOBALS['language']->newsletter['notify_not_subscribed']);
        }
        return $removed;
    }
    /**
     * Set unsubscribe headers
     *
     * @param string $email
     */
    public function unsubscribe_header($email): void
    {
        $this->_mailer->clear_custom_headers();
        $this->_mailer->add_custom_header('List-Unsubscribe', '<' . $GLOBALS['storeURL'] . '/index.php?_a=unsubscribe&unsubscribe=' . urlencode($email) . '>');
        $this->_mailer->add_custom_header('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
    }
    /**
     * Double opt in newsletter subscription
     *
     * @param string $validation
     */
    public function double_opt_in($validation = false): bool
    {
        // Verify the validation email
        if (!empty($validation)) {
            $validate = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', ['subscriber_id', 'email'], ['validation' => $validation], false, 1, false, false);
            if ($validate) {
                $this->_subscriber_log($validate[0]['email'], 'Double opt-in verified');
                $GLOBALS['db']->update('CubeCart_newsletter_subscriber', ['dbl_opt' => '1', 'date' => date('c'), 'ip_address' => get_ip_address()], ['subscriber_id' => $validate[0]['subscriber_id']]);
                foreach ($GLOBALS['hooks']->load('class.newsletter.validated') as $hook) {
                    include $hook;
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Validate email address and MX record
     *
     * @param string $email
     * @return 0, 1, 2
     */
    public function validate_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            [$user, $domain] = explode('@', $email);
            if (!isset($this->_validated_domain[$domain])) {
                return $this->_validated_domain[$domain] = (int) checkdnsrr($domain, 'MX');
            }
            return $this->_validated_domain[$domain];
        }
        return 2;
    }
    /**
     * Log subscription status
     *
     * @param string $email
     * @return bool
     */
    private function _subscriber_log($email, string $log)
    {
        if (!empty($email) && !empty($log)) {
            return $GLOBALS['db']->insert('CubeCart_newsletter_subscriber_log', ['email' => htmlentities((string) $email, ENT_QUOTES, 'UTF-8'), 'log' => $log, 'ip_address' => get_ip_address()]);
        }
        return false;
    }
}