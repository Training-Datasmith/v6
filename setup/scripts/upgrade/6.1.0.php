<?php

declare(strict_types=1);
$config_string = $db->select('CubeCart_config', ['array'], ['name' => 'config']);
$config = json_decode(base64_decode($config_string[0]['array']), true);

$added_config = [
  'r_admin_activity' => '30',
  'r_admin_error' => '30',
  'r_email' => '30',
  'r_request' => '14',
  'r_staff' => '30',
  'r_system_error' => '7',
];

$db->update('CubeCart_config', ['array' => base64_encode(json_encode(array_merge($config, $added_config)))], ['name' => 'config']);
