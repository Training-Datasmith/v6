<?php

declare(strict_types=1);
$config_string = $db->select('CubeCart_config', ['array'], ['name' => 'config']);
$config = json_decode(base64_decode($config_string[0]['array']), true);
$added_config = ['time_format' => 'j M Y, H:i', 'fuzzy_time_format' => 'H:i', 'dispatch_date_format' => 'M d Y'];
$db->update('CubeCart_config', ['array' => base64_encode(json_encode(array_merge($config, $added_config)))], ['name' => 'config']);
# This code is duplicated in admin/sources/categories.inc.php
function updateCatsWithHierPosition($cat_id = 0, $position = 0)
{
    global $db;
    if ($cat_id == 0) {
        $db->update('CubeCart_category', ['cat_hier_position' => 0]);
        $cats = $db->select('CubeCart_category', ['cat_id'], ['cat_parent_id' => 0], ['priority' => 'ASC']);
    } else {
        $cats = $db->select('CubeCart_category', ['cat_id'], ['cat_parent_id' => $cat_id], ['priority' => 'ASC']);
    }
    if (isset($cats) && is_array($cats) && !empty($cats)) {
        foreach ($cats as $cat) {
            if ($position > 0) {
                $db->update('CubeCart_category', ['cat_hier_position' => $position], ['cat_id' => $cat['cat_id']]);
            }
            updateCatsWithHierPosition($cat['cat_id'], ($position + 1));
        }
    }
}
updateCatsWithHierPosition();
