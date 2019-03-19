<?php

namespace QUI\Verification;

use QUI;

/**
 * Class Cron
 *
 * Cron class for quiqqer/verification
 */
class Cron
{
    /**
     * Delete all verified Verification entries from database
     * that have been verified a certain amount of days before now
     *
     * @return void
     * @throws \QUI\Exception
     */
    public static function deleteVerified()
    {
        $result = QUI::getDataBase()->fetch([
            'select' => [
                'id',
                'verifiedDate'
            ],
            'from'   => Verifier::getDatabaseTable(),
            'where'  => [
                'verified' => 1
            ]
        ]);

        $deleteIds   = [];
        $Conf        = QUI::getPackage('quiqqer/verification')->getConfig();
        $verifiedTtl = (int)$Conf->get('settings', 'keepVerifiedDuration'); // days
        $verifiedTtl *= 24 * 60 * 60; // seconds
        $now         = time();

        foreach ($result as $row) {
            $verifiedTime = strtotime($row['verifiedDate']);
            $aliveTime    = $now - $verifiedTime;

            if ($aliveTime > $verifiedTtl) {
                $deleteIds[] = $row['id'];
            }
        }

        if (empty($deleteIds)) {
            return;
        }

        QUI::getDataBase()->delete(
            Verifier::getDatabaseTable(),
            [
                'id' => [
                    'type'  => 'IN',
                    'value' => $deleteIds
                ]
            ]
        );
    }

    /**
     * Delete all unverified Verification entries from database
     * that have exceeded their "valid until" date
     *
     * @return void
     * @throws \QUI\Exception
     */
    public static function deleteUnverified()
    {
        $result = QUI::getDataBase()->fetch([
            'select' => [
                'id'
            ],
            'from'   => Verifier::getDatabaseTable(),
            'where'  => [
                'verified'       => 0,
                'validUntilDate' => [
                    'type'  => '<=',
                    'value' => date('Y-m-d H:i:s')
                ]
            ]
        ]);

        $deleteIds = [];

        foreach ($result as $row) {
            $deleteIds[] = $row['id'];
        }

        if (empty($deleteIds)) {
            return;
        }

        QUI::getDataBase()->delete(
            Verifier::getDatabaseTable(),
            [
                'id' => [
                    'type'  => 'IN',
                    'value' => $deleteIds
                ]
            ]
        );
    }
}
