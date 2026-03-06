<?php

declare(strict_types=1);
if (!isset($glob['cache'])) {
    $glob['cache'] = 'file';
    writeGlobalConfig($glob, $global_file);
}
