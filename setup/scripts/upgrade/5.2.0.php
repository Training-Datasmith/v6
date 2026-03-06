<?php

declare(strict_types=1);
$config_string = $db->select('CubeCart_config', ['array'], ['name' => 'config']);
$config = json_decode(base64_decode($config_string[0]['array']), true);
if (!is_array($config)) {
    $config = [];
}
$added_config = [];
$added_config['product_sort_column']   = 'name';
$added_config['product_sort_direction']  = 'ASC';
$db->update('CubeCart_config', ['array' => base64_encode(json_encode(array_merge($config, $added_config)))], ['name' => 'config']);
