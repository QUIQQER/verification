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
     */
    public static function deleteVerified()
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => array(
                'id',
                'verifiedDate'
            ),
            'from'   => Verifier::getDatabaseTable(),
            'where'  => array(
                'verified' => 1
            )
        ));

        $deleteIds   = array();
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
            array(
                'id' => array(
                    'type'  => 'IN',
                    'value' => $deleteIds
                )
            )
        );
    }
}
