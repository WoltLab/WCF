<?php

/**
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

// change the priority of the PIPs to "1"
$sql = "UPDATE  wcf1_package_installation_plugin
        SET     priority = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([1]);

// Clear any outdated cached data from WCFSetup.
UserStorageHandler::getInstance()->clear();

// get server timezone
if ($timezone = @\date_default_timezone_get()) {
    if (\in_array($timezone, DateUtil::getAvailableTimezones())) {
        $sql = "UPDATE  wcf1_option
                SET     optionValue = ?
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $timezone,
            'timezone',
        ]);
    }
}
