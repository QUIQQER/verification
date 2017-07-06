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
    try {
        $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_INVALID_REQUEST);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification getErrorMessage error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }
} else {
    // if hash is correct, check validUntilDate
    $validUntil = strtotime($verificationData['validUntilDate']);

    if (time() <= $validUntil) {
        try {
            $msg = $VerificationClass::getSuccessMessage($identifier);
        } catch (\Exception $Exception) {
            QUI\System\Log::addError(
                'Verification getSuccessMessage error: "'
                . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
            );

            QUI\System\Log::writeException($Exception);
        }

        $success = true;
    } else {
        try {
            $msg = $VerificationClass::getErrorMessage($identifier, Verifier::ERROR_REASON_EXPIRED);
        } catch (\Exception $Exception) {
            QUI\System\Log::addError(
                'Verification getErrorMessage error: "'
                . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
            );

            QUI\System\Log::writeException($Exception);
        }
    }

    // delete verification from db
    Verifier::finishVerification($verificationId);
}

// VERIFICATION SUCCESS
if ($success) {
    try {
        $VerificationClass::onSuccess($identifier);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification onSuccess error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    if (empty($msg)) {
        $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.success');
    }

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
// VERIFICATION ERROR
} else {
    try {
        $VerificationClass::onError($identifier);
    } catch (\Exception $Exception) {
        QUI\System\Log::addError(
            'Verification onError error: "'
            . $verificationData['source'] . '" (identifier: ' . $identifier . ')'
        );

        QUI\System\Log::writeException($Exception);
    }

    if (empty($msg)) {
        $msg = QUI::getLocale()->get('quiqqer/verification', 'message.types.verifier.error.general');
    }

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
}

$Engine->assign(array(
    'msg'     => $msg,
    'success' => $success
));
