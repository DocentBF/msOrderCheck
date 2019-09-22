<?php
/** @var xPDOTransport $transport */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    if(!$modx->loadClass('transport.xPDOObjectVehicle', XPDO_CORE_PATH, true, true)){
        $modx->log(1, print_r("Cannot load XPDOObjectVehicle class",1));
    }

    //if(!$modx->loadClass('EncryptedVehicle', MODX_CORE_PATH . 'components/' . strtolower($transport->name) . '/', true, true)) {
    if(!$modx->loadClass('EncryptedVehicle', MODX_CORE_PATH . 'components/modstoreencrypt/', true, true)) {
        $modx->log(1, print_r("Cannot load EncryptedVehicle class",1));
    }

}