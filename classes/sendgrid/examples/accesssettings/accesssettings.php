<?php

declare (strict_types=1);
// Next line will load dependencies to run this example
// Please refer to the README how to use in your project
require_once __DIR__ . '/../../sendgrid-php.php';
$api_key = getenv('SENDGRID_API_KEY');
$sg = new \Send_Grid($api_key);
////////////////////////////////////////////////////
// Retrieve all recent access attempts #
// GET /access_settings/activity #
$query_params = json_decode('{"limit": 1}');
try {
    $response = $sg->client->access_settings()->activity()->get(null, $query_params);
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// Add one or more IPs to the whitelist #
// POST /access_settings/whitelist #
$request_body = json_decode('{
  "ips": [
    {
      "ip": "192.168.1.1"
    },
    {
      "ip": "192.*.*.*"
    },
    {
      "ip": "192.168.1.3/32"
    }
  ]
}');
try {
    $response = $sg->client->access_settings()->whitelist()->post($request_body);
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// Retrieve a list of currently whitelisted IPs #
// GET /access_settings/whitelist #
try {
    $response = $sg->client->access_settings()->whitelist()->get();
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// Remove one or more IPs from the whitelist #
// DELETE /access_settings/whitelist #
$request_body = json_decode('{
  "ids": [
    1,
    2,
    3
  ]
}');
try {
    $response = $sg->client->access_settings()->whitelist()->delete($request_body);
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// Retrieve a specific whitelisted IP #
// GET /access_settings/whitelist/{rule_id} #
$rule_id = 'test_url_param';
try {
    $response = $sg->client->access_settings()->whitelist()->_($rule_id)->get();
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}
////////////////////////////////////////////////////
// Remove a specific IP from the whitelist #
// DELETE /access_settings/whitelist/{rule_id} #
$rule_id = 'test_url_param';
try {
    $response = $sg->client->access_settings()->whitelist()->_($rule_id)->delete();
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}