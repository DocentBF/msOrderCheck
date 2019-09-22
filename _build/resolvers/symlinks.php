<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/msOrderCheck/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/msordercheck')) {
            $cache->deleteTree(
                $dev . 'assets/components/msordercheck/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/msordercheck/', $dev . 'assets/components/msordercheck');
        }
        if (!is_link($dev . 'core/components/msordercheck')) {
            $cache->deleteTree(
                $dev . 'core/components/msordercheck/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/msordercheck/', $dev . 'core/components/msordercheck');
        }
    }
}

return true;