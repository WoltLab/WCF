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
$sql = "UPDATE  wcf" . WCF_N . "_package_installation_plugin
        SET     priority = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);

// Clear any outdated cached data from WCFSetup.
UserStorageHandler::getInstance()->clear();

// get server timezone
if ($timezone = @\date_default_timezone_get()) {
    if ($timezone != 'Europe/London' && \in_array($timezone, DateUtil::getAvailableTimezones())) {
        $sql = "UPDATE  wcf" . WCF_N . "_option
                SET     optionValue = ?
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $timezone,
            'timezone',
        ]);
    }
}
