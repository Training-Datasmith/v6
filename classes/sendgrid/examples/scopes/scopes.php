<?php

declare (strict_types=1);
// Next line will load dependencies to run this example
// Please refer to the README how to use in your project
require_once __DIR__ . '/../../sendgrid-php.php';
$api_key = getenv('SENDGRID_API_KEY');
$sg = new \Send_Grid($api_key);
////////////////////////////////////////////////////
// Retrieve a list of scopes for which this user has access. #
// GET /scopes #
try {
    $response = $sg->client->scopes()->get();
    print $response->status_code() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ', $e->get_message(), "\n";
}