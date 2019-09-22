<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$msOrderCheck = $modx->getService('msOrderCheck', 'msOrderCheck', $modx->getOption('msordercheck_core_path', null,
        $modx->getOption('core_path') . 'components/msordercheck/') . 'model/', array());
//$modx->lexicon->load('msordercheck:default');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    $modx->sendRedirect($modx->makeUrl($modx->getOption('site_start'), '', '', 'full'));
} elseif (empty($_REQUEST['msoch_token'])) {
    echo $msOrderCheck->error('msordercheck_err_token_ns');
} else {
    echo $msOrderCheck->process($_REQUEST);
}