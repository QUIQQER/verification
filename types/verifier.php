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
$msg            = false;
$errorReason    = Verifier::ERROR_REASON_INVALID_REQUEST;

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
$expected          = Encryption::decrypt($verificationData['verificationHash']);

if ($verificationData['verified']) {
    $errorReason = Verifier::ERROR_REASON_ALREADY_VERIFIED;
} elseif ($_REQUEST['hash'] === $expected) {
    // if hash is correct, check validUntilDate
    $validUntil = strtotime($verificationData['validUntilDate']);

    if (time() <= $validUntil) {
        $success = true;
    } else {
        $errorReason = Verifier::ERROR_REASON_EXPIRED;
    }

    // delete verification from db
    Verifier::finishVerification($verificationId);
}

// VERIFICATION SUCCESS
if ($success) {
    // execute onSuccess
    try {
        $VerificationClass::onSuccess($identifier);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification onSuccess error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    // onSuccess redirect
    try {
        $redirect = $VerificationClass::getOnSuccessRedirectUrl($identifier);

        if ($redirect) {
            redirect($redirect);
        }
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification getOnSuccessRedirectUrl error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    try {
        $msg = $VerificationClass::getSuccessMessage($identifier);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification getSuccessMessage error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    if (empty($msg)) {
        $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.success');
    }
// VERIFICATION ERROR
} else {
    // execute onError
    try {
        $VerificationClass::onError($identifier);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification onError error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    // onError redirect
    try {
        $redirect = $VerificationClass::getOnErrorRedirectUrl($identifier);

        if ($redirect) {
            redirect($redirect);
        }
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification getOnErrorRedirectUrl error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    // get error message
    try {
        $msg = $VerificationClass::getErrorMessage($identifier, $errorReason);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification getErrorMessage error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    if (empty($msg)) {
        $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.' . $errorReason);
    }
}

$Engine->assign(array(
    'msg'     => $msg,
    'success' => $success
));
