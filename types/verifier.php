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

/** @var \QUI\Verification\VerificationInterface $Verification */
$Verification = new $VerificationClass($identifier, $verificationData['additionalData']);
$expected     = Encryption::decrypt($verificationData['verificationHash']);

if ($_REQUEST['hash'] === $expected) {
    if ($verificationData['verified']) {
        $errorReason = Verifier::ERROR_REASON_ALREADY_VERIFIED;
    } else {
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
}

// VERIFICATION SUCCESS
if ($success) {
    // execute onSuccess
    try {
        $Verification->onSuccess();
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification onSuccess error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    // onSuccess redirect
    try {
        $redirect = $Verification->getOnSuccessRedirectUrl();

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
        $msg = $Verification->getSuccessMessage();
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
        $Verification->onError();
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification onError error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    // onError redirect
    try {
        $redirect = $Verification->getOnErrorRedirectUrl();

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
        $msg = $Verification->getErrorMessage($errorReason);
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
