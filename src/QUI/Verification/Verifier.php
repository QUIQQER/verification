<?php

namespace QUI\Verification;

use QUI;

/**
 * Class Verifier
 *
 * Main class that starts verification processes and executes certain
 * methods on successfull verification or shows errors on failed verification
 */
class Verifier
{
    const SITE_TYPE = 'quiqqer/verification:types/verifier';

    /**
     * Start a verification process
     *
     * @param VerificationInterface $Verification
     * @return string - Verification URL
     *
     * @throws QUI\Verification\Exception
     */
    public static function startVerification(VerificationInterface $Verification)
    {
        if (self::existsVerification($Verification)) {
            throw new QUI\Verification\Exception(array(
                'quiqqer/verification',
                'exception.verifier.verification.already.exists',
                array(
                    'identifier' => $Verification->getIdentifier()
                )
            ));
        }

        // calculate duration
        $end = strtotime(
            self::getFormattedTimestamp() . ' +' . $Verification::getValidDuration() . ' minute'
        );

        $hash = self::generateVerificationHash();
        $url  = self::getVerifierSite()->getUrlRewritten(array(), array(
            'identifier' => $Verification->getIdentifier(),
            'hash'       => $hash
        ));

        QUI::getDataBase()->insert(self::getDatabaseTable(), array(
            'identifier'       => $Verification->getIdentifier(),
            'verificationHash' => $hash,
            'createDate'       => self::getFormattedTimestamp(),
            'validUntilDate'   => self::getFormattedTimestamp($end),
            'source'           => get_class($Verification)
        ));

        return $url;
    }

    /**
     * Verify a verification based on a request
     *
     * @param string $identifier - Verification identifier
     * @param string $hash - Verification hash
     * @return array - Data of correct Verification
     *
     * @throws QUI\Verification\Exception
     */
    public static function verify($identifier, $hash)
    {
        $result = QUI::getDataBase()->fetch(array(
            'from'  => self::getDatabaseTable(),
            'where' => array(
                'identifier' => $identifier
            )
        ));

        if (empty($result)) {
            throw new QUI\Verification\Exception(array(
                'quiqqer/verification',
                'exception.verifier.verification.does.not.exists'
            ));
        }

        $verificationData = false;

        foreach ($result as $row) {
            if ($row['hash'] === $hash) {
                $verificationData = $row;
                break;
            }
        }

        if ($verificationData === false) {
            throw new QUI\Verification\Exception(array(
                'quiqqer/verification',
                'exception.verifier.verification.invalid.hash'
            ));
        }

        return $verificationData;
    }

    /**
     * Get verifier Site
     *
     * @return QUI\Projects\Site|QUI\Projects\Site\Edit
     * @throws QUI\Verification\Exception
     */
    protected static function getVerifierSite()
    {
        $Project = QUI::getProjectManager()->getStandard();
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
        $result = array(
            'select' => array(
                'id'
            ),
            'from'   => self::getDatabaseTable(),
            'where'  => array(
                'identifier' => $Verification->getIdentifier(),
                'source'     => get_class($Verification)
            )
        );

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
    protected static function getDatabaseTable()
    {
        return QUI::getDBTableName('quiqqer_verification');
    }
}
