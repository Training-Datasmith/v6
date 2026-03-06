<?php

declare(strict_types=1);
$config_string = $db->select('CubeCart_config', ['array'], ['name' => 'gift_certs']);
$config = json_decode(base64_decode($config_string[0]['array']), true);
if (is_numeric($config['product_code'])) {
    $added_config = ['product_code' => 'GC'.$config['product_code']];
    $db->update('CubeCart_config', ['array' => base64_encode(json_encode(array_merge($config, $added_config)))], ['name' => 'gift_certs']);
}

$config_string = $db->select('CubeCart_config', ['array'], ['name' => 'config']);
$config = json_decode(base64_decode($config_string[0]['array']), true);
$added_config = ['seo_ext' => '.html'];
$db->update('CubeCart_config', ['array' => base64_encode(json_encode(array_merge($config, $added_config)))], ['name' => 'config']);

$filename = CC_ROOT_DIR.'/.htaccess';
$content = file_get_contents($filename);
$content_chunks = explode('^(.*)\.html?', $content);
$content = implode('^(.*)?', $content_chunks);
file_put_contents($filename, $content);
