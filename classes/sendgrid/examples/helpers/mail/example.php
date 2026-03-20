<?php

declare (strict_types=1);
// Next line will load dependencies to run this example
// Please refer to the README how to use in your project
require_once __DIR__ . '/../../../sendgrid-php.php';
use Send_Grid\Mail\Asm;
use Send_Grid\Mail\Attachment;
use Send_Grid\Mail\Bcc;
use Send_Grid\Mail\Bcc_Settings;
use Send_Grid\Mail\Bypass_Bounce_Management;
use Send_Grid\Mail\Bypass_List_Management;
use Send_Grid\Mail\Bypass_Spam_Management;
use Send_Grid\Mail\Bypass_Unsubscribe_Management;
use Send_Grid\Mail\Cc;
use Send_Grid\Mail\Click_Tracking;
use Send_Grid\Mail\Content;
use Send_Grid\Mail\Custom_Arg;
use Send_Grid\Mail\Footer;
use Send_Grid\Mail\From;
use Send_Grid\Mail\Ganalytics;
use Send_Grid\Mail\Header;
use Send_Grid\Mail\Mail;
use Send_Grid\Mail\Mail_Settings;
use Send_Grid\Mail\Open_Tracking;
use Send_Grid\Mail\Personalization;
use Send_Grid\Mail\Reply_To;
use Send_Grid\Mail\Sand_Box_Mode;
use Send_Grid\Mail\Send_At;
use Send_Grid\Mail\Spam_Check;
use Send_Grid\Mail\Subject;
use Send_Grid\Mail\Subscription_Tracking;
use Send_Grid\Mail\To;
use Send_Grid\Mail\Tracking_Settings;
function hello_email(): ?\Send_Grid\Mail\Mail
{
    try {
        $from = new From('test@example.com', 'Twilio Sendgrid');
        $subject = 'Hello World from the Twilio SendGrid PHP Library';
        $to = new To('test@example.com');
        $content = new Content('text/plain', 'some text here');
        $mail = new Mail($from, $to, $subject, $content);
        $personalization = new Personalization();
        $personalization->add_to(new To('test2@example.com'));
        $personalization->add_from(new From('test3@example.com', 'Twilio Sendgrid'));
        $mail->add_personalization($personalization);
        //echo json_encode($mail, JSON_PRETTY_PRINT), "\n";
        return $mail;
    } catch (\Exception $e) {
        echo $e->get_message();
    }
    return null;
}
function kitchen_sink(): ?\Send_Grid\Mail\Mail
{
    try {
        $from = new From('test@example.com', 'Twilio SendGrid');
        $subject = 'Hello World from the Twilio SendGrid PHP Library';
        $to = new To('test1@example.com', 'Example User');
        $content = new Content('text/plain', 'some text here');
        $mail = new Mail($from, $to, $subject, $content);
        $personalization0 = new Personalization();
        $personalization0->add_to(new To('test2@example.com', 'Example User'));
        $personalization0->add_from(new From('test3@example.com', 'Twilio SendGrid'));
        $personalization0->add_cc(new Cc('test4@example.com', 'Example User'));
        $personalization0->add_cc(new Cc('test5@example.com', 'Example User'));
        $personalization0->add_bcc(new Bcc('test6@example.com', 'Example User'));
        $personalization0->add_bcc(new Bcc('test7@example.com', 'Example User'));
        $personalization0->set_subject(new Subject('Hello World from the Twilio SendGrid PHP Library'));
        $personalization0->add_header(new Header('X-Test', 'test'));
        $personalization0->add_header(new Header('X-Mock', 'true'));
        $personalization0->add_substitution('%name%', 'Example User');
        $personalization0->add_substitution('%city%', 'Denver');
        $personalization0->add_substitution('%sec1%', '%section1%');
        $personalization0->add_custom_arg(new Custom_Arg('user_id', '343'));
        $personalization0->add_custom_arg(new Custom_Arg('type', 'marketing'));
        $personalization0->set_send_at(new Send_At(1443636843));
        $mail->add_personalization($personalization0);
        $personalization1 = new Personalization();
        $personalization1->add_to(new To('test8@example.com', 'Example User'));
        $personalization1->add_to(new To('test9@example.com', 'Example User'));
        $personalization1->add_from(new From('test10@example.com', 'Twilio SendGrid'));
        $personalization1->add_cc(new Cc('test11@example.com', 'Example User'));
        $personalization1->add_cc(new Cc('test12@example.com', 'Example User'));
        $personalization1->add_bcc(new Bcc('test13@example.com', 'Example User'));
        $personalization1->add_bcc(new Bcc('test14@example.com', 'Example User'));
        $personalization1->set_subject(new Subject('Hello World from the Twilio SendGrid PHP Library'));
        $personalization1->add_header(new Header('X-Test', 'test'));
        $personalization1->add_header(new Header('X-Mock', 'true'));
        $personalization1->add_substitution('%name%', 'Example User');
        $personalization1->add_substitution('%city%', 'Denver');
        $personalization1->add_substitution('%sec2%', '%section2%');
        $personalization1->add_custom_arg(new Custom_Arg('user_id', '343'));
        $personalization1->add_custom_arg(new Custom_Arg('type', 'marketing'));
        $personalization1->set_send_at(new Send_At(1443636843));
        $mail->add_personalization($personalization1);
        // Examples of adding personalization by specifying personalization indexes
        $mail->add_cc('test15@example.com', 'Example User', null, 0);
        $mail->add_bcc('test16@example.com', 'Example User', null, 1);
        $content = new Content('text/html', '<html><body>some text here</body></html>');
        $mail->add_content($content);
        $attachment = new Attachment();
        $attachment->set_content('TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQsIGNvbnNlY3RldHVyIGFkaXBpc2NpbmcgZWxpdC4gQ3JhcyBwdW12');
        $attachment->set_type('application/pdf');
        $attachment->set_filename('balance_001.pdf');
        $attachment->set_disposition('attachment');
        $attachment->set_content_id('Balance Sheet');
        $mail->add_attachment($attachment);
        $attachment2 = new Attachment();
        $attachment2->set_content('BwdW');
        $attachment2->set_type('image/png');
        $attachment2->set_filename('banner.png');
        $attachment2->set_disposition('inline');
        $attachment2->set_content_id('Banner');
        $mail->add_attachment($attachment2);
        $mail->set_template_id('439b6d66-4408-4ead-83de-5c83c2ee313a');
        # This must be a valid [batch ID](https://sendgrid.com/docs/API_Reference/SMTP_API/scheduling_parameters.html) to work
        # $mail->setBatchID("sendgrid_batch_id");
        $mail->add_section('%section1%', 'Substitution Text for Section 1');
        $mail->add_section('%section2%', 'Substitution Text for Section 2');
        $mail->add_header('X-Test1', '1');
        $mail->add_header('X-Test2', '2');
        $mail->add_category('May');
        $mail->add_category('2016');
        $mail->add_custom_arg('campaign', 'welcome');
        $mail->add_custom_arg('weekday', 'morning');
        $mail->set_send_at(1443636842);
        $asm = new ASM();
        $asm->set_group_id(99);
        $asm->set_groups_to_display([4, 5, 6, 7, 8]);
        $mail->set_asm($asm);
        $mail->set_ip_pool_name('23');
        $mail_settings = new Mail_Settings();
        $bcc_settings = new Bcc_Settings();
        $bcc_settings->set_enable(true);
        $bcc_settings->set_email('test@example.com');
        $mail_settings->set_bcc_settings($bcc_settings);
        $sandbox_mode = new Sand_Box_Mode();
        $sandbox_mode->set_enable(true);
        $mail_settings->set_sandbox_mode($sandbox_mode);
        // Note: Bypass Spam, Bounce, and Unsubscribe management cannot
        // be combined with Bypass List Management
        $bypass_bounce_management = new Bypass_Bounce_Management();
        $bypass_bounce_management->set_enable(true);
        $mail_settings->set_bypass_bounce_management($bypass_bounce_management);
        $bypass_list_management = new Bypass_List_Management();
        $bypass_list_management->set_enable(true);
        $mail_settings->set_bypass_list_management($bypass_list_management);
        $bypass_spam_management = new Bypass_Spam_Management();
        $bypass_spam_management->set_enable(true);
        $mail_settings->set_bypass_spam_management($bypass_spam_management);
        $bypass_unsubscribe_management = new Bypass_Unsubscribe_Management();
        $bypass_unsubscribe_management->set_enable(true);
        $mail_settings->set_bypass_unsubscribe_management($bypass_unsubscribe_management);
        $footer = new Footer();
        $footer->set_enable(true);
        $footer->set_text('Footer Text');
        $footer->set_html('<html><body>Footer Text</body></html>');
        $mail_settings->set_footer($footer);
        $spam_check = new Spam_Check();
        $spam_check->set_enable(true);
        $spam_check->set_threshold(1);
        $spam_check->set_post_to_url('https://spamcatcher.sendgrid.com');
        $mail_settings->set_spam_check($spam_check);
        $mail->set_mail_settings($mail_settings);
        $tracking_settings = new Tracking_Settings();
        $click_tracking = new Click_Tracking();
        $click_tracking->set_enable(true);
        $click_tracking->set_enable_text(true);
        $tracking_settings->set_click_tracking($click_tracking);
        $open_tracking = new Open_Tracking();
        $open_tracking->set_enable(true);
        $open_tracking->set_substitution_tag('Optional tag to replace with the open image in the body of the message');
        $tracking_settings->set_open_tracking($open_tracking);
        $subscription_tracking = new Subscription_Tracking();
        $subscription_tracking->set_enable(true);
        $subscription_tracking->set_text('text to insert into the text/plain portion of the message');
        $subscription_tracking->set_html('<html><body>html to insert into the text/html portion of the message</body></html>');
        $subscription_tracking->set_substitution_tag('Optional tag to replace with the open image in the body of the message');
        $tracking_settings->set_subscription_tracking($subscription_tracking);
        $ganalytics = new Ganalytics();
        $ganalytics->set_enable(true);
        $ganalytics->set_campaign_source('some source');
        $ganalytics->set_campaign_term('some term');
        $ganalytics->set_campaign_content('some content');
        $ganalytics->set_campaign_name('some name');
        $ganalytics->set_campaign_medium('some medium');
        $tracking_settings->set_ganalytics($ganalytics);
        $mail->set_tracking_settings($tracking_settings);
        $reply_to = new Reply_To('test@example.com', 'Optional Name');
        $mail->set_reply_to($reply_to);
        //echo json_encode($mail, JSON_PRETTY_PRINT), "\n";
        return $mail;
    } catch (\Exception $e) {
        echo 'Caught exception: ', $e->get_message(), "\n";
    }
    return null;
}
function send_hello_email(): void
{
    $api_key = getenv('SENDGRID_API_KEY');
    $sg = new \Send_Grid($api_key);
    $request_body = hello_email();
    if (!$request_body instanceof Mail) {
        echo 'Invalid request_body to send HelloEmail', "\n";
        return;
    }
    try {
        $response = $sg->client->mail()->send()->post($request_body);
        print $response->status_code() . "\n";
        print_r($response->headers());
        print $response->body() . "\n";
    } catch (\Exception $e) {
        echo 'Caught exception: ', $e->get_message(), "\n";
    }
}
function send_kitchen_sink(): void
{
    $api_key = getenv('SENDGRID_API_KEY');
    $sg = new \Send_Grid($api_key);
    $request_body = kitchen_sink();
    if (!$request_body instanceof Mail) {
        echo 'Invalid request_body to send KitchenSink', "\n";
        return;
    }
    try {
        $response = $sg->client->mail()->send()->post($request_body);
        print $response->status_code() . "\n";
        print_r($response->headers());
        print $response->body() . "\n";
    } catch (\Exception $e) {
        echo 'Caught exception: ', $e->get_message(), "\n";
    }
}
send_hello_email();
// this will actually send an email
send_kitchen_sink();
// this will only send an email if you set SandBox Mode to false