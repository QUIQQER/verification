<?php

use QUI\Verification\Verifier;
use QUI\Security\Encryption;

function redirect($target)
{
    header('Location: ' . $target);
    exit;
}

if (empty($_REQUEST['hash'])
    || empty($_REQUEST['verificationId'])
) {
    $Engine->assign(array(
        'msg'     => QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.general'),
        'success' => false
    ));

    return;
}

$success        = false;
$verificationId = (int)$_REQUEST['verificationId'];

try {
    $verificationData = Verifier::getVerificationData($verificationId);
} catch (\Exception $Exception) {
    $Engine->assign(array(
        'msg'     => QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.general'),
        'success' => false
    ));

    return;
}

/** @var \QUI\Verification\VerificationInterface $VerificationClass */
$VerificationClass = $verificationData['source'];
$identifier        = $verificationData['identifier'];

// verify data against hash
$expected = Encryption::decrypt($verificationData['verificationHash']);

if ($_REQUEST['hash'] !== $expected) {
    $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_INVALID_REQUEST);
} else {
    // if hash is correct, check validUntilDate
    $validUntil = strtotime($verificationData['validUntilDate']);

    if (time() <= $validUntil) {
        $msg     = $VerificationClass::getSuccessMessage($identifier);
        $success = true;
    } else {
        $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_EXPIRED);
    }

    // delete from db
    Verifier::finishVerification($verificationId);
}

if ($success) {
    $VerificationClass::onSuccess($identifier);

    if (empty($msg)) {
        $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.success');
    }

    $redirect = $VerificationClass::getOnSuccessRedirectUrl($identifier);

    if ($redirect) {
        redirect($redirect);
    }
} else {
    $VerificationClass::onError($identifier);

    if (empty($msg)) {
        $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.general');
    }

    $redirect = $VerificationClass::getOnErrorRedirectUrl($identifier);

    if ($redirect) {
        redirect($redirect);
    }
}

$Engine->assign(array(
    'msg'     => $msg,
    'success' => $success
));
