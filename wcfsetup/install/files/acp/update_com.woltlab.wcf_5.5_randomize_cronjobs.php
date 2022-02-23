<?php

/**
 * Randomize the times of the package list update and robot list update cronjobs.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$sql = "UPDATE  wcf1_cronjob
        SET     startMinute = ?,
                startHour = ?,
                startDom = ?,
                startMonth = ?,
                startDow = ?,
                lastExec = ?,
                nextExec = ?,
                afterNextExec = ?
        WHERE   packageiD = ?
            AND cronjobName = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    \random_int(0, 59),
    \random_int(0, 23),
    '*',
    '*',
    '*',

    0,
    \TIME_NOW,
    0,

    $this->installation->getPackageID(),
    'com.woltlab.wcf.refreshPackageUpdates',
]);
$statement->execute([
    \random_int(0, 59),
    \random_int(0, 23),
    \random_int(1, 15),
    '*',
    '*',

    0,
    \TIME_NOW,
    0,

    $this->installation->getPackageID(),
    'com.woltlab.wcf.refreshSearchRobots',
]);
