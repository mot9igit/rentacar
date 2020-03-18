<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/rentacar/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/rentacar')) {
            $cache->deleteTree(
                $dev . 'assets/components/rentacar/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/rentacar/', $dev . 'assets/components/rentacar');
        }
        if (!is_link($dev . 'core/components/rentacar')) {
            $cache->deleteTree(
                $dev . 'core/components/rentacar/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/rentacar/', $dev . 'core/components/rentacar');
        }
    }
}

return true;