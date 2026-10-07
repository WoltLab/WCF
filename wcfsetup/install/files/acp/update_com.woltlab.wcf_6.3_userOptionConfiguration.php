<?php

/**
 * Builds the form option configuration of the existing user options and converts
 * the legacy option types that have an equivalent form option. The `facebook` and
 * `twitter` options as well as the options of the legacy type `labeledUrl` become
 * social media profiles.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\system\WCF;

// The social media profiles of the core, which were text options with a custom output.
$socialMediaProfiles = [
    'facebook' => [
        'profileNamePattern' => '^[A-Za-z\d.]{5,}$',
        'profileUrlPattern' => 'https://www.facebook.com/%s',
    ],
    'twitter' => [
        'profileNamePattern' => '^[A-Za-z0-9_]+$',
        'profileUrlPattern' => 'https://x.com/%s',
    ],
];

$sql = "SELECT  optionID, optionName, optionType, selectOptions, required, validationPattern, labeledUrl
        FROM    wcf1_user_option";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$options = [];
while ($row = $statement->fetchArray()) {
    $configuration = UserOptionBuilder::buildConfiguration(
        $row['selectOptions'] ?? '',
        (bool)$row['required']
    );
    $optionType = UserOption::LEGACY_OPTION_TYPES[$row['optionType']] ?? $row['optionType'];
    $validationPattern = $row['validationPattern'] ?? '';

    $profileConfiguration = [];
    if ($row['optionType'] === 'labeledUrl') {
        $profileConfiguration['profileUrlPattern'] = $row['labeledUrl'] ?? '';
    } elseif ($row['optionType'] === 'text' && isset($socialMediaProfiles[$row['optionName']])) {
        $optionType = 'socialMedia';
        $profileConfiguration = $socialMediaProfiles[$row['optionName']];

        // The name pattern replaces the validation pattern.
        $validationPattern = '';
    }

    if ($profileConfiguration !== []) {
        $configuration = \json_encode(\array_merge(
            $configuration !== '' ? \json_decode($configuration, true, flags: \JSON_THROW_ON_ERROR) : [],
            $profileConfiguration
        ), \JSON_THROW_ON_ERROR);
    }

    $options[$row['optionID']] = [
        'configuration' => $configuration,
        'optionType' => $optionType,
        'validationPattern' => $validationPattern,
        'hasNewlineSeparatedValues' => $row['optionType'] === 'checkboxes' || $row['optionType'] === 'multiSelect',
    ];
}

$sql = "UPDATE  wcf1_user_option
        SET     configuration = ?,
                optionType = ?,
                validationPattern = ?
        WHERE   optionID = ?";
$statement = WCF::getDB()->prepare($sql);

WCF::getDB()->beginTransaction();
foreach ($options as $optionID => $option) {
    $statement->execute([
        $option['configuration'],
        $option['optionType'],
        $option['validationPattern'],
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
