<?php

use QUI\Verification\Verifier;

/**
 * Send 401 status code if anything goes wrong
 */
function send401()
{
    $Response = QUI::getGlobalResponse();
    $Response->setStatusCode(401, 'Unauthorized or malformed request');
    $Response->send();

    exit;
}

if (empty($_REQUEST['hash'])
    || empty($_REQUEST['identifier'])
) {
    send401();
}

try {
    $verificationData = Verifier::verify($_REQUEST['identifier'], $_REQUEST['hash']);
} catch (\Exception $Exception) {

}
