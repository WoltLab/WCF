<?php

/**
 * Builds the form option configuration of the existing user options and converts
 * the legacy option types that have an equivalent form option.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\system\WCF;

$sql = "SELECT  optionID, optionType, selectOptions, required
        FROM    wcf1_user_option";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$options = [];
while ($row = $statement->fetchArray()) {
    $options[$row['optionID']] = [
        'configuration' => UserOptionBuilder::buildConfiguration(
            $row['selectOptions'] ?? '',
            (bool)$row['required']
        ),
        'optionType' => UserOption::LEGACY_OPTION_TYPES[$row['optionType']] ?? $row['optionType'],
        'hasNewlineSeparatedValues' => $row['optionType'] === 'checkboxes' || $row['optionType'] === 'multiSelect',
    ];
}

$sql = "UPDATE  wcf1_user_option
        SET     configuration = ?,
                optionType = ?
        WHERE   optionID = ?";
$statement = WCF::getDB()->prepare($sql);

WCF::getDB()->beginTransaction();
foreach ($options as $optionID => $option) {
    $statement->execute([
        $option['configuration'],
        $option['optionType'],
        $optionID,
    ]);
}
WCF::getDB()->commitTransaction();

// The legacy multiple selection option types separate the selected values by
// newlines, the `checkboxes` form option separates them by commas.
foreach ($options as $optionID => $option) {
    if (!$option['hasNewlineSeparatedValues']) {
        continue;
    }

    $column = 'userOption' . $optionID;
    $sql = "UPDATE  wcf1_user_option_value
            SET     {$column} = REPLACE(REPLACE({$column}, ?, ?), ?, ?)
            WHERE   {$column} LIKE ?";
    $statement = WCF::getDB()->prepare($sql);
    $statement->execute([
        "\r\n",
        "\n",
        "\n",
        ',',
        "%\n%",
    ]);
}

UserOptionBuilder::resetCache();
