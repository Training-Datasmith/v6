<?php

declare(strict_types=1);
$targets = [
    ['CubeCart_option_group', 'option_id', 'option_name'],
    ['CubeCart_option_value', 'value_id', 'value_name'],
];

foreach ($targets as $target) {
    if ($rec = $db->select($target[0], [$target[1]], false, [$target[2] => 'ASC'])) {
        $r = 0;
        foreach ($rec as $reco) {
            $db->update($target[0], ['priority' => ++$r], [$target[1] => $reco[$target[1]]]);
        }
    }
}
