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

$startMinute = \random_int(0, 59);
$startHour = \random_int(0, 23);

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
    $startMinute,
    $startHour,
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
    $startMinute,
    (($startHour + 12) % 24),
    \random_int(1, 15),
    '*',
    '*',

    0,
    \TIME_NOW,
    0,

    $this->installation->getPackageID(),
    'com.woltlab.wcf.refreshSearchRobots',
]);
