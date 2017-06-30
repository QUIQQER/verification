<?php

use QUI\Verification\Verifier;
use QUI\Security\Encryption;

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

    if (empty($msg)) {
        $Engine->assign(array(
            'msg'     => QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.general'),
            'success' => false
        ));

        $VerificationClass::onSuccess($identifier);
        return;
    }
} else {
    // if hash is correct, check validUntilDate
    $validUntil = strtotime($verificationData['validUntilDate']);

    if (time() <= $validUntil) {
        $msg = $VerificationClass::getSuccessMessage($identifier);

        if (empty($msg)) {
            $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.success');
        }

        $VerificationClass::onSuccess($identifier);
        $success = true;
    } else {
        $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_EXPIRED);

        if (empty($msg)) {
            $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.expired');
        }

        $VerificationClass::onError($identifier);
    }

    // delete from db
    Verifier::finishVerification($verificationId);
}

$Engine->assign(array(
    'msg'     => $msg,
    'success' => $success
));
