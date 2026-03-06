<?php

declare(strict_types=1);
## Delete "unordered" js files to prevent duplication
$js_path = CC_ROOT_DIR.'/skins/foundation/js/';

$files = ['foundation.min.js', 'cubecart.js', 'cubecart.validate.js'];
foreach ($files as $file) {
    @unlink($js_path.$file);
}
foreach ($files as $file) {
    if (file_exists($js_path.$file)) {
        $errors[] = 'Please delete the file skins/foundation/js/'.$file;
    }
}
