<?php

declare (strict_types=1);
// index.php
require_once __DIR__ . '/../../sendgrid-php.php';
use Send_Grid\Mail\Mail;
use Send_Grid\Mail\Personalization;
use Send_Grid\Mail\To;
$exception_message = 'Caught exception: ';
////////////////////////////////////////////////////
// Set data residency to navigate to a region/edge. #
// sending to global data residency
$email = build_hello_email();
$sendgrid = build_sendgrid_object('global');
try {
    $response = $sendgrid->client->mail()->send()->post($email);
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo $exception_message, $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// sending to EU data residency
$sendgrid_eu = build_sendgrid_object('eu');
try {
    $response = $sendgrid_eu->client->mail()->send()->post($email);
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo $exception_message, $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// not configuring any region defaults to global
$sendgrid_default = new \Send_Grid(getenv('SENDGRID_API_KEY'));
try {
    $response = $sendgrid_default->client->mail()->send()->post($email);
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo $exception_message, $e->get_message(), "\n";
}
function build_hello_email(): Mail
{
    $email = new Mail();
    $email->set_from('test@example.com', 'test');
    $email->set_subject('Sending with Twilio SendGrid is Fun');
    $email->add_to('test@example.co', 'test');
    $email->add_content('text/plain', 'and easy to do anywhere, even with PHP');
    $email->add_content('text/html', '<strong>and easy to do anywhere, even with PHP</strong>');
    $obj_personalization = new Personalization();
    $obj_to = new To('foo@bar.com', 'foo bar');
    $obj_personalization->add_to($obj_to);
    $email->add_personalization($obj_personalization);
    return $email;
}
function build_sendgrid_object($region): Send_Grid
{
    $sendgrid = new \Send_Grid(getenv('SENDGRID_API_KEY'));
    $sendgrid->set_data_residency($region);
    return $sendgrid;
}