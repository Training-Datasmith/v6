<?php

declare(strict_types=1);
$cs = $db->select('CubeCart_config', ['array'], ['name' => 'config']);
$c = json_decode(base64_decode($cs[0]['array']), true);
$c['standard_url']  = preg_replace('#^http://#', 'https://', $c['standard_url']);
$db->update('CubeCart_config', ['array' => base64_encode(json_encode($c))], ['name' => 'config']);
// We are using PHP instead of the SQL file for the following changes to avoid errors if they have already been applied
if (!$db->misc('SHOW KEYS FROM `'.$glob['dbprefix']."CubeCart_sessions` WHERE Key_name = 'PRIMARY' AND Column_name = 'id'")) {
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_sessions` DROP PRIMARY KEY');
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_sessions` ADD KEY (`session_id`)');
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_sessions` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)');
}
if (!$db->misc('SHOW KEYS FROM `'.$glob['dbprefix']."CubeCart_seo_urls` WHERE Key_name = 'PRIMARY' AND Column_name = 'id'")) {
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_seo_urls` DROP PRIMARY KEY');
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_seo_urls` ADD PRIMARY KEY(`id`)');
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_seo_urls` DROP INDEX `id`');
    $db->misc('ALTER TABLE `'.$glob['dbprefix'].'CubeCart_seo_urls` ADD UNIQUE (`path`)');
}
