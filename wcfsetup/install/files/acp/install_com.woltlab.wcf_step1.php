<?php

/**
 * @author Tim Duesterhus, Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use ParagonIE\ConstantTime\Hex;
use wcf\data\option\OptionEditor;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

// change the priority of the PIPs to "1"
$sql = "UPDATE  wcf1_package_installation_plugin
        SET     priority = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([1]);

// Clear any outdated cached data from WCFSetup.
UserStorageHandler::getInstance()->clear();

// Configure early dynamic option values

$sql = "UPDATE  wcf1_option
        SET     optionValue = ?
        WHERE   optionName = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    StringUtil::getUUID(),
    'wcf_uuid',
]);

$statement->execute([
    Hex::encode(\random_bytes(20)),
    'signature_secret',
]);

if (\file_exists(WCF_DIR . 'cookiePrefix.txt')) {
    $statement->execute([
        COOKIE_PREFIX,
        'cookie_prefix',
    ]);

    @\unlink(WCF_DIR . 'cookiePrefix.txt');
}

if ($timezone = @\date_default_timezone_get()) {
    if (\in_array($timezone, DateUtil::getAvailableTimezones())) {
        $statement->execute([
            $timezone,
            'timezone',
        ]);
    }
}

OptionEditor::resetCache();
