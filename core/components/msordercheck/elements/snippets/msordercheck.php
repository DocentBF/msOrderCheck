<?php
if (!$modx->loadClass('msOrderCheck',  $modx->getOption('msordercheck_core_path', null, $modx->getOption('core_path') . 'components/msordercheck/') . 'model/', false, true)) {
    return false;
}
$msOrderCheck = new msOrderCheck($modx, $scriptProperties);
$scriptProperties['fields'] = $msOrderCheck->getValidFields();
if(empty($fields)) {
    $modx->log(modX::LOG_LEVEL_ERROR, $modx->lexicon('msordercheck_err_fields_ns'));
    return false;
}

$tplForm = $modx->getOption('tplForm', $scriptProperties, 'tpl.msOrderCheck.form');
$content = $msOrderCheck->getChunk($tplForm, $scriptProperties);

if(empty($content)) {
    print_r($scriptProperties);
    return false;
}
// load css and js
$msOrderCheck->loadAssets();
$token = bin2hex(openssl_random_pseudo_bytes(32));

// set settings to session
$_SESSION['msOrderCheck']['settings'] = array($token => $scriptProperties);

$tokenInput = '<input type="hidden" name="msoch_token" value="' . $token . '" />';
// add csrf token
if ((stripos($content, '</form>') !== false)) {
    if (preg_match('#<input.*?name=(?:"|\')msoch_token(?:"|\').*?>#i', $content, $matches)) {
        $content = str_ireplace($matches[0], '', $content);
    }
    $content = str_ireplace('</form>', "\n\t$tokenInput\n</form>", $content);
}

return $content;