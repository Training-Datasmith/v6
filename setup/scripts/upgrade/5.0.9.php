<?php

declare(strict_types=1);
## fix any broken SEO paths
if ($seo_paths = $db->select('CubeCart_seo_urls', ['id', 'path'])) {
    foreach ($seo_paths as $seo_path) {
        $db->update('CubeCart_seo_urls', ['path' => SEO::sanitizeSEOPath($seo_path['path'])], ['id' => $seo_path['id']]);
    }
}
