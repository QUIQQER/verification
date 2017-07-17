<?php

namespace QUI\Verification;

use QUI;
use QUI\Security\Encryption;

/**
 * Class Verifier
 *
 * Main class that starts verification processes and executes certain
 * methods on successfull verification or shows errors on failed verification
 */
class Verifier
{
    /**
     * Verifier site type
     */
    const SITE_TYPE = 'quiqqer/verification:types/verifier';

    /**
     * Error reasons
     */
    const ERROR_REASON_INVALID_REQUEST  = 'invalid_request';
    const ERROR_REASON_EXPIRED          = 'expired';
    const ERROR_REASON_ALREADY_VERIFIED = 'already_verified';

    /**
     * Start a verification process
     *
     * @param VerificationInterface $Verification
     * @param bool $overwriteExisting (optional) - Overwrite Verification with identical
     * identifier and source class [default: false]
     * @return string - Verification URL
     *
     * @throws QUI\Verification\Exception
     */
    public static function startVerification(VerificationInterface $Verification, $overwriteExisting = false)
    {
        if (self::existsVerification($Verification)) {
            if ($overwriteExisting !== true) {
                throw new QUI\Verification\Exception(array(
                    'quiqqer/verification',
                    'exception.verifier.verification.already.exists',
                    array(
                        'identifier' => $Verification->getIdentifier()
                    )
                ));
            }

            self::removeVerification($Verification);
        }

        $validDuration = (int)$Verification::getValidDuration($Verification->getIdentifier());

        // fallback
        if (empty($validDuration)) {
            $Conf          = QUI::getPackage('quiqqer/verification')->getConfig();
            $validDuration = $Conf->get('settings', 'validDuration');
        }

        // calculate duration
        $end = strtotime(
            self::getFormattedTimestamp() . ' +' . $validDuration . ' minute'
        );

        $hash         = self::generateVerificationHash();
        $VerifierSite = self::getVerifierSite();

        QUI::getDataBase()->insert(self::getDatabaseTable(), array(
            'identifier'       => $Verification->getIdentifier(),
            'verificationHash' => Encryption::encrypt($hash),
            'createDate'       => self::getFormattedTimestamp(),
            'validUntilDate'   => self::getFormattedTimestamp($end),
            'source'           => get_class($Verification)
        ));

        $url = $VerifierSite->getProject()->getVHost(true) . $VerifierSite->getUrlRewritten(array(), array(
                'verificationId' => QUI::getPDO()->lastInsertId(),
                'hash'           => $hash
            ));

        return $url;
    }

    /**
     * Verify a verification based on a request
     *
     * @param int $verificationId - Verification ID
     * @return array - Data of correct Verification
     *
     * @throws QUI\Verification\Exception
     */
    public static function getVerificationData($verificationId)
    {
        $result = QUI::getDataBase()->fetch(array(
            'from'  => self::getDatabaseTable(),
            'where' => array(
                'id' => $verificationId
            )
        ));

        if (empty($result)) {
            throw new QUI\Verification\Exception(array(
                'quiqqer/verification',
                'exception.verifier.verification.does.not.exists'
            ));
        }

        return current($result);
    }

    /**
     * Delete Verification from database
     *
     * @param VerificationInterface $Verification
     * @return void
     */
    public static function removeVerification(VerificationInterface $Verification)
    {
        QUI::getDataBase()->delete(
            self::getDatabaseTable(),
            array(
                'identifier' => $Verification->getIdentifier(),
                'source'     => get_class($Verification)
            )
        );
    }

    /**
     * Finish Verification process
     *
     * @param int $verificationId - Verification ID
     */
    public static function finishVerification($verificationId)
    {
        QUI::getDataBase()->update(
            self::getDatabaseTable(),
            array(
                'verified'     => 1,
                'verifiedDate' => self::getFormattedTimestamp()
            ),
            array(
                'id' => $verificationId
            )
        );
    }

    /**
     * Get verifier Site
     *
     * @return QUI\Projects\Site|QUI\Projects\Site\Edit
     * @throws QUI\Verification\Exception
     */
    protected static function getVerifierSite()
    {
        $Project = QUI::getRewrite()->getProject();
        $siteIds = $Project->getSitesIds(array(
            'where' => array(
                'type' => self::SITE_TYPE
            )
        ));

        if (empty($siteIds)) {
            throw new QUI\Verification\Exception(array(
                'quiqqer/verification',
                'exception.verifier.site.does.not.exist'
            ));
        }

        return $Project->get($siteIds[0]['id']);
    }

    /**
     * Check if a verification is already existing in the database
     *
     * @param VerificationInterface $Verification
     * @return bool
     */
    protected static function existsVerification(VerificationInterface $Verification)
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => array(
                'id'
            ),
            'from'   => self::getDatabaseTable(),
            'where'  => array(
                'identifier' => $Verification->getIdentifier(),
                'source'     => get_class($Verification)
            )
        ));

        return !empty($result);
    }

    /**
     * Generates a new, random verification hash
     *
     * @return string
     */
    protected static function generateVerificationHash()
    {
        return md5(openssl_random_pseudo_bytes(256));
    }

    /**
     * Get formatted timestamp for a given UNIX timestamp
     *
     * @param int $time (optional) - if omitted use time()
     * @return string
     */
    protected static function getFormattedTimestamp($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }

        return date('Y-m-d H:i:s', $time);
    }

    /**
     * Get name of verification database table
     *
     * @return string
     */
    public static function getDatabaseTable()
    {
        return QUI::getDBTableName('quiqqer_verification');
    }
}
