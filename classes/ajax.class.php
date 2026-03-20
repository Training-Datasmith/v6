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
 * AJAX controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Ajax
{
    //=====[ Public ]=======================================
    /**
     * Load the proper AJAX function/method
     */
    public static function load()
    {
        global $glob;
        $json = '';
        //Kill debug
        $GLOBALS['debug']->supress();
        //Try a hook first
        foreach ($GLOBALS['hooks']->load('class.ajax.load') as $hook) {
            include $hook;
        }
        if (!empty($json)) {
            return $json;
        }
        //Get the correct function/method
        $type = $_GET['type'] ?? '';
        $string = $_GET['q'] ?: '';
        $function = isset($_GET['function']) && !empty($_GET['function']) ? $_GET['function'] : 'search';
        return match ($function) {
            'rebuildElasticsearch' => self::rebuild_elasticsearch(),
            'filesize' => self::filesize((string) $_GET['path'], 0),
            'fmSearch' => self::fm_search(),
            'doneToggle' => self::done_toggle(),
            'viewEmail' => self::view_email((int) $_GET['id'], (string) $_GET['mode']),
            'SMTPTest' => self::smtp_test(),
            'previewOrderFormat' => self::preview_order_format(),
            'template' => self::template($type, $string),
            'subscriber_log' => self::subscriber_log(),
            'seopath' => self::seopath($_GET['type'], $_GET['item_id']),
            default => self::search($type, $string),
        };
    }
    public static function done_toggle()
    {
        $allowed_tables = ['404_log'];
        $table = isset($_POST['table']) && in_array($_POST['table'], $allowed_tables, true) ? $_POST['table'] : null;
        if ($table === null) {
            return json_encode(['success' => '0', 'id' => 0, 'status' => '0']);
        }
        if ($_POST['status'] == 'warn') {
            $update = ['warn' => 0];
        } else {
            $status = $_POST['status'] == '1' ? 0 : 1;
            $update = ['done' => $status];
            if ($status == 0) {
                $update['warn'] = 0;
            }
        }
        if ($GLOBALS['db']->update('CubeCart_' . $table, $update, ['id' => (int) $_POST['id']])) {
            return json_encode(['success' => '1', 'id' => (int) $_POST['id'], 'status' => (string) $_POST['status']]);
        }
        return json_encode(['success' => '0', 'id' => (int) $_POST['id'], 'status' => (string) $_POST['status']]);
    }
    /**
     * Rebuild ElasticSearch
     *
     * @param string $path
     * @param int $item_id
     * @return json string
     */
    public static function rebuild_elastic_search()
    {
        $es = new Elasticsearch_Handler();
        $status = $es->rebuild($_GET['page']);
        if (is_array($status)) {
            $data = $status;
        } else {
            $data = $status ? ['complete' => 'true', 'percent' => 100] : ['error' => 'true'];
        }
        return json_encode($data);
    }
    /**
     * Get directory size
     *
     * @param string $path
     * @param int $item_id
     * @return json string
     */
    public static function seopath($type, $item_id)
    {
        return json_encode($GLOBALS['seo']->getdb_path($type, $item_id));
    }
    /**
     * Get directory size
     *
     * @return data/false
     */
    public static function filesize(string $path, $total)
    {
        return json_encode(dirsize(CC_ROOT_DIR . '/' . $path, $total));
    }
    /**
     * Admin search function
     *
     * @param string $type
     * @param string $search_string
     * @return data/false
     */
    public static function search($type, ?string $search_string)
    {
        $data = [];
        foreach ($GLOBALS['hooks']->load('class.ajax.search') as $hook) {
            include $hook;
        }
        if (!empty($type) && !empty($search_string)) {
            switch (strtolower($type)) {
                case 'user':
                    if (($results = $GLOBALS['db']->select('CubeCart_customer', false, ['~' . $search_string => ['last_name', 'first_name', 'email']], false, false, false, false)) !== false) {
                        foreach ($results as $result) {
                            $data[] = ['value' => $result['customer_id'], 'display' => $result['first_name'] . ' ' . $result['last_name'], 'info' => $result['email'], 'data' => $result];
                        }
                    }
                    break;
                case 'address':
                    if (($results = $GLOBALS['db']->select('CubeCart_addressbook', false, ['customer_id' => (int) $search_string], false, false, false, false)) !== false) {
                        foreach ($results as $result) {
                            $result['state'] = get_state_format($result['state']);
                            $result['country'] = get_country_format($result['country']);
                            $result['description'] = empty($result['description']) ? $result['line1'] . ', ' . $result['postcode'] : $result['description'];
                            $data[] = $result;
                        }
                    }
                    break;
                case 'product':
                    // Limited to a maximum of 15 results, in order to prevent it going mental
                    if (($results = $GLOBALS['db']->select('CubeCart_inventory', false, ['~' . $search_string => ['name', 'product_code']], false, 15, false, false)) !== false) {
                        foreach ($results as $result) {
                            $lower_price = Tax::get_instance()->sale_price($result['price'], $result['sale_price'], false);
                            if ($lower_price && $lower_price < $result['price']) {
                                $result['price'] = $lower_price;
                            }
                            $data[] = ['value' => $result['product_id'], 'display' => $result['name'], 'info' => Tax::get_instance()->price_format($result['price']), 'data' => $result];
                        }
                    }
                    break;
                case 'newsletter':
                    $newsletter = Newsletter::get_instance();
                    $status = $newsletter->send_newsletter($_GET['q'], $_GET['page']);
                    if (is_array($status)) {
                        $data = $status;
                    } else {
                        $data = $status ? ['complete' => 'true', 'percent' => 100] : ['error' => 'true'];
                    }
                    break;
                case 'files':
                    if ($_GET['dir'] == '/') {
                        $dir = false;
                    } elseif ($_GET['dir'] == '/') {
                        $dir = false;
                    } else {
                        $dir = $_GET['dir'];
                    }
                    $filemanager = new File_Manager($_GET['group'], $dir);
                    // Directories
                    $dirs = $filemanager->get_directories();
                    if (is_array($dirs)) {
                        $dir = $filemanager->format_path($dir) ?? '';
                        if (isset($dirs[$dir]) && !empty($dirs[$dir])) {
                            foreach ($dirs[$dir] as $folder) {
                                $path = !empty($dir) ? '/' : '';
                                $json[] = ['type' => 'directory', 'path' => urldecode($dir . basename((string) $folder) . '/'), 'name' => basename((string) $folder)];
                            }
                        }
                    }
                    $assigned_images = [];
                    $assigned_file = false;
                    if (isset($_GET['product_id'])) {
                        $assigned_images = $filemanager->product_images($_GET['product_id']);
                        $assigned_file = $filemanager->product_file($_GET['product_id']);
                    }
                    if (isset($_GET['cat_id'])) {
                        $assigned_images = $filemanager->cat_images($_GET['cat_id']);
                    }
                    if (isset($_GET['unique_image'])) {
                        $assigned_images = $filemanager->unique_image($_GET['unique_image']);
                    }
                    if (($files = $filemanager->list_files()) !== false) {
                        $catalogue = $GLOBALS['catalogue']->get_instance();
                        foreach ($files as $result) {
                            if ($filemanager->get_mode() == File_Manager::FM_FILETYPE_IMG) {
                                $fetch = $catalogue->image_path($result['file_id'], 'medium');
                                $path = $name = $fetch;
                            } else {
                                $path = $result['filepath'];
                                $name = $result['filename'];
                            }
                            $assigned = '0';
                            if (isset($assigned_images[$result['file_id']])) {
                                $assigned = $assigned_images[$result['file_id']];
                            } elseif ($assigned_file && $assigned_file == $result['file_id']) {
                                $assigned = '1';
                            }
                            $json[] = ['form_field' => $filemanager->form_fields, 'assigned' => $assigned, 'type' => 'file', 'type_id' => $result['type'], 'path' => dirname((string) $path) . '/', 'file' => basename((string) $result['filename']), 'name' => basename((string) $name), 'id' => $result['file_id'], 'description' => $result['description'], 'mime' => $result['mimetype']];
                        }
                    }
                    $data = isset($json) && is_array($json) ? $json : false;
                    break;
                default:
                    ## See https://github.com/cubecart/v6/issues/3191
                    foreach ($GLOBALS['hooks']->load('class.ajax.search.case_default') as $hook) {
                        include $hook;
                    }
                    break;
            }
            if (!$data) {
                $data = [];
            }
            return json_encode($data);
        }
        return false;
    }
    /**
     * Preview Order Format
     *
     * @return data/false
     */
    public static function preview_order_format(): string|false
    {
        if (CC_IN_ADMIN) {
            if ($_GET['oid_mode'] == 't') {
                $html_out = '<h3>Preview sample of next 5 Orders</h3>';
                for ($i = 1; $i <= 5; $i++) {
                    $html_out .= date('ymd-His-') . random_int(1000, 9999) . '<br>';
                }
                $html_out .= '<p>Plese note that the last four digits are random.</p>';
            } else {
                $html_out = '<h3>Preview of next 5 Orders</h3>';
                $next = $GLOBALS['db']->select('CubeCart_order_summary', 'MAX(`id`) as `max_oid`');
                $order = Order::get_instance();
                $next[0]['max_oid']++;
                for ($i = $next[0]['max_oid']; $i <= $next[0]['max_oid'] + 5; $i++) {
                    $html_out .= $order->set_order_format($_GET['oid_prefix'], $_GET['oid_postfix'], $_GET['oid_zeros'], $_GET['oid_start'], false, false, $i) . '<br>';
                }
            }
            return '<div class="default_modal">' . $html_out . '</div>';
        }
        return false;
    }
    /**
     * Search for image/digital download
     *
     * @return data/false
     */
    public static function fm_search()
    {
        if (CC_IN_ADMIN) {
            $term = $_POST['term'];
            if (strlen((string) $term) >= 3) {
                $mode = $_POST['mode'] == 'digital' ? 'digital' : 'images';
                $action = $_POST['action'] == 'show' ? 'show' : 'location';
                $path = $_POST['mode'] == 'digital' ? CC_ROOT_DIR . '/files' : CC_ROOT_DIR . '/images/source';
                $files = fm_search_list($path, $term);
                $output = '';
                foreach ($files as $file) {
                    if ($mode == 'images') {
                        $preview = '<div><img src="' . str_replace(CC_ROOT_DIR . '/', CC_ROOT_REL, $file) . '" /></div>';
                    } else {
                        $preview = '';
                    }
                    $file = str_replace($path . '/', '', $file);
                    $subdir = urlencode(ltrim(dirname($file), '/'));
                    $anchor = md5(urlencode(basename($file)));
                    if ($action == 'show') {
                        $output .= "<li class=\"show\"><a href=\"?_g=filemanager&subdir={$subdir}&mode={$mode}&" . time() . "&file_id=file_{$anchor}\">{$preview} {$file}</a></li>";
                    } else {
                        $output .= "<li>{$preview} {$file}</li>";
                    }
                }
                if (empty($output)) {
                    $output = '<p>' . $GLOBALS['language']->common['error_no_results'] . '</p>';
                } else {
                    $output = "<ul class=\"fmsearch_{$mode}\">{$output}</ul>";
                }
            } else {
                $output = '<p>' . $GLOBALS['language']->filemanager['min_two_char'] . '</p>';
            }
            return '<div class="mail_modal"><h3>' . sprintf($GLOBALS['language']->filemanager['search_result'], $_POST['term']) . "</h3>{$output}</div>";
        }
    }
    /**
     * Test SMPT
     *
     * @return data/false
     */
    public static function smtp_test(): string|false
    {
        if (CC_IN_ADMIN) {
            $methods = ['mail' => $GLOBALS['language']->settings['email_method_mail'], 'smtp' => $GLOBALS['language']->settings['email_method_smtp'], 'smtp_ssl' => $GLOBALS['language']->settings['email_method_smtp_ssl'], 'smtp_tls' => $GLOBALS['language']->settings['email_method_smtp_tls'], 'sendgrid' => 'SendGrid'];
            $method_name = $methods[$GLOBALS['RAW']['POST']['email_method']];
            $subject = 'Testing ' . $method_name;
            $body = 'Testing email sent by &quot;' . $method_name . '&quot; from CubeCart v' . CC_VERSION . ' at ' . CC_STORE_URL . '.<br><br>If you are reading this message then you can be sure that email from your store is working.';
            $altbody = strip_tags($body);
            $test_success = false;
            if ($GLOBALS['RAW']['POST']['email_method'] == 'sendgrid') {
                require_once CC_ROOT_DIR . '/classes/sendgrid/sendgrid-php.php';
                $test_mailer = new \Send_Grid\Mail\Mail();
                $test_mailer->set_from($GLOBALS['RAW']['POST']['email_address'], html_entity_decode((string) $GLOBALS['RAW']['POST']['email_name'], ENT_QUOTES));
                $test_mailer->set_subject($subject);
                $test_mailer->add_to($GLOBALS['RAW']['POST']['email_address'], html_entity_decode((string) $GLOBALS['RAW']['POST']['email_name'], ENT_QUOTES));
                $test_mailer->add_content('text/plain', $altbody);
                $test_mailer->add_content('text/html', $body);
                $sendgrid = new \Send_Grid($GLOBALS['RAW']['POST']['sendgrid_key']);
                $json = '<h3>Testing ' . $method_name . ' - {{RESULT}}</h3>';
                try {
                    $response = $sendgrid->send($test_mailer);
                    $json .= '<strong>Response Code:</strong> ' . $response->status_code() . '<br>';
                    $json .= '<strong>Response Headers:</strong><br>';
                    foreach ($response->headers() as $h) {
                        $json .= $h . '<br>';
                    }
                    $json .= $response->body() . '<br>';
                    if (str_starts_with($response->status_code(), '2')) {
                        $test_success = true;
                    }
                } catch (Exception $e) {
                    $json .= $e->get_message();
                }
                return '<div class="default_modal">' . str_replace('{{RESULT}}', $test_success ? 'Success' : 'Fail', $json) . '</div>';
            }
            if ($GLOBALS['RAW']['POST']['email_method'] !== 'mail') {
                @ob_start();
                $test_mailer = new Mailer();
                $test_mailer->smtp_debug = 2;
                $test_mailer->Debugoutput = 'html';
                $test_mailer->clear_addresses();
                $test_mailer->From = $GLOBALS['RAW']['POST']['email_address'];
                $test_mailer->from_name = html_entity_decode((string) $GLOBALS['RAW']['POST']['email_name'], ENT_QUOTES);
                $test_mailer->Host = $GLOBALS['RAW']['POST']['email_smtp_host'];
                $test_mailer->Port = $GLOBALS['RAW']['POST']['email_smtp_port'];
                if ($GLOBALS['RAW']['POST']['email_method'] == 'smtp_ssl') {
                    $test_mailer->smtp_secure = 'ssl';
                } elseif ($GLOBALS['RAW']['POST']['email_method'] == 'smtp_tls') {
                    $test_mailer->smtp_secure = 'tls';
                }
                if ($GLOBALS['RAW']['POST']['email_smtp'] == '1') {
                    $test_mailer->smtp_auth = true;
                    $test_mailer->Username = $GLOBALS['RAW']['POST']['email_smtp_user'];
                    $test_mailer->Password = $GLOBALS['RAW']['POST']['email_smtp_password'];
                }
                $test_mailer->add_address($GLOBALS['RAW']['POST']['email_address']);
                $test_mailer->Subject = $subject;
                $test_mailer->Body = $body;
                $test_mailer->alt_body = $altbody;
                // Send email
                $test_success = $test_mailer->Send();
                $test_mailer_test_results = @ob_get_contents();
                @ob_end_clean();
                $json = '<h3>Testing ' . $method_name . ' - {{RESULT}}</h3>';
                if (!empty($test_mailer_test_results)) {
                    $test_mailer_test_results_data = ['request_url' => 'mailto:' . $GLOBALS['RAW']['POST']['email_address'], 'request' => 'Subject: Testing CubeCart', 'result' => $test_mailer_test_results, 'error' => $test_success ? null : 'Mailer Failed'];
                    $GLOBALS['db']->insert('CubeCart_request_log', $test_mailer_test_results_data);
                    $json .= $test_mailer_test_results;
                } else {
                    $json .= 'Test failed to execute. ' . $test_mailer->error_info;
                }
                return '<div class="default_modal">' . str_replace('{{RESULT}}', $test_success ? 'Success' : 'Fail', $json) . '</div>';
            }
            $test_mailer = new Mailer();
            $test_mailer->clear_addresses();
            $test_mailer->add_address($GLOBALS['RAW']['POST']['email_address']);
            $test_mailer->Subject = $subject;
            $test_mailer->Body = $body;
            $test_mailer->alt_body = $altbody;
            $test_mailer->Send();
            return '<div class="default_modal"><h3>Testing ' . $method_name . "</h3><p>It isn't possible  to get a definitive test result for the &quot;PHP mail() Function&quot; method.</p><p>We have attempted to send a test email to &quot;" . $GLOBALS['RAW']['POST']['email_address'] . '&quot; with the subject of &quot;' . $subject . "&quot; Please note that it can take ten minutes or even longer for a busy mail server to deliver email. Don't forget to check your spam folder!</p><p>This method can fail if the server hasn't been configured properly and may refuse to send mail from &quot;untrusted&quot; sources such as Hotmail, Yahoo, AOL etc&hellip;. We recommend using an email address from a domain hosted on this server such as sales@" . parse_url(CC_STORE_URL, PHP_URL_HOST) . ' for example and this may need to be setup form within your web hosting account.</p></div>';
        }
        return false;
    }
    /**
     * Subscriber consent log
     *
     * @return data/false
     */
    public static function subscriber_log(): string|false
    {
        if (CC_IN_ADMIN) {
            if (filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
                $html_out = '<h3>Log for ' . $_GET['email'] . '</h3>';
                if ($logs = $GLOBALS['db']->select('CubeCart_newsletter_subscriber_log', false, ['email' => $_GET['email']])) {
                    foreach ($logs as $log) {
                        $html_out .= '<strong>' . $log['date'] . ' - ' . $log['ip_address'] . '</strong><br>' . $log['log'] . '<br>';
                    }
                    $html_out .= '<p style="text-align:center"><a href="?_g=customers&node=subscribers&delete_log=' . urlencode((string) $_GET['email']) . '" class="delete">Delete Log</a></p>';
                } else {
                    $html_out .= '<p>No logs found.</p>';
                }
            } else {
                $html_out .= '<p>Invalid email</p>';
            }
            return '<div class="default_modal">' . $html_out . '</div>';
        }
        return false;
    }
    /**
     * Dynamic template load
     *
     * @param string $type
     * @param string $search_string
     * @return data/false
     */
    public static function template($type, $search_string): bool
    {
        switch (strtolower($type)) {
            case 'prod_options':
                $options['options'] = Catalogue::get_instance()->display_product_options($search_string);
                $GLOBALS['smarty']->assign('product', $options);
                die($GLOBALS['smarty']->fetch('templates/element.product_options.php'));
        }
        return false;
    }
    /**
     * View Email
     *
     * @param int $id
     * @return data/false
     */
    public static function view_email($id, $mode)
    {
        $column = $mode == 'content_text' ? 'content_text' : 'content_html';
        if (CC_IN_ADMIN) {
            $test_mailer = $GLOBALS['db']->select('CubeCart_email_log', [$column], ['id' => $id]);
            if ($mode == 'content_text') {
                return '<div style="font-family: \'Courier New\', Courier">' . nl2br(htmlentities((string) $test_mailer[0][$column], ENT_QUOTES)) . '</div>';
            }
            return $test_mailer[0][$column];
        }
        return false;
    }
}