<?php

declare (strict_types=1);
// Next line will load dependencies to run this example
// Please refer to the README how to use in your project
require_once __DIR__ . '/../../../sendgrid-php.php';
$api_key = getenv('SENDGRID_API_KEY');
$sg = new \Send_Grid($api_key);
// Provide date in YYYY-MM-DD format
$stats = new \Send_Grid\Stats\Stats('2017-10-18');
//$response = $sg->client->categories()->post(['category' => 'cat2']);
//$response = $sg->client->categories()->get(null, $query_params);
$global_response = $sg->client->stats()->get(null, $stats->get_global());
$category_response = $sg->client->categories()->stats()->get(null, $stats->get_category(['category1', 'category2']));
$category_sum_response = $sg->client->categories()->stats()->sums()->get(null, $stats->get_sum());
$subuser_response = $sg->client->subusers()->stats()->get(null, $stats->get_subuser(['user1', 'user2']));
$subuser_sum_response = $sg->client->subusers()->stats()->sums()->get(null, $stats->get_sum());
$subuser_monthly_response = $sg->client->subusers()->stats()->monthly()->get(null, $stats->get_subuser_monthly());
var_dump($global_response);
var_dump($category_response);
var_dump($category_response);
var_dump($subuser_response);
var_dump($subuser_sum_response);
var_dump($subuser_monthly_response);