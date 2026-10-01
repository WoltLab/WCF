<?php

/**
 * Builds the form option configuration of the existing user options.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\user\option\UserOptionEditor;
use wcf\system\WCF;

$sql = "SELECT  optionID, selectOptions, required
        FROM    wcf1_user_option";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$configurations = [];
while ($row = $statement->fetchArray()) {
    $configurations[$row['optionID']] = UserOptionEditor::buildConfiguration(
        $row['selectOptions'] ?? '',
        (bool)$row['required']
    );
}

$sql = "UPDATE  wcf1_user_option
        SET     configuration = ?
        WHERE   optionID = ?";
$statement = WCF::getDB()->prepare($sql);

WCF::getDB()->beginTransaction();
foreach ($configurations as $optionID => $configuration) {
    $statement->execute([
        $configuration,
        $optionID,
    ]);
}
WCF::getDB()->commitTransaction();

UserOptionEditor::resetCache();
