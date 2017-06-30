<?php

use QUI\Verification\Verifier;
use QUI\Security\Encryption;

/**
 * Send 401 status code if anything goes wrong
 */
function sendGeneralError()
{
    global $Engine;

    $Engine->assign(array(
        'msg'     => QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.general'),
        'success' => false
    ));
}

if (empty($_REQUEST['hash'])
    || empty($_REQUEST['verificationId'])
) {
    sendGeneralError();
    return;
}

$success        = false;
$verificationId = (int)$_REQUEST['verificationId'];

try {
    $verificationData = Verifier::getVerificationData($verificationId);
} catch (\Exception $Exception) {
    sendGeneralError();
    return;
}

/** @var \QUI\Verification\VerificationInterface $VerificationClass */
$VerificationClass = $verificationData['source'];
$identifier        = $verificationData['identifier'];

// verify data against hash
$expected = Encryption::decrypt($verificationData['hash']);

if ($_REQUEST['hash'] !== $expected) {
    $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_INVALID_REQUEST);

    if (empty($msg)) {
        sendGeneralError();
    }

    return;
} else {
    // if hash is correct, check validUntilDate
    $validUntil = strtotime($verificationData['validUntilDate']);

    if (time() <= $validUntil) {
        $msg = $VerificationClass::getSuccessMessage($identifier);

        if (empty($msg)) {
            $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.success');
        }

        $success = true;
    } else {
        $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_EXPIRED);

        if (empty($msg)) {
            $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.expired');
        }
    }

    // delete from db
    Verifier::finishVerification($verificationId);
}

$Engine->assign(array(
    'msg'     => $msg,
    'success' => $success
));
