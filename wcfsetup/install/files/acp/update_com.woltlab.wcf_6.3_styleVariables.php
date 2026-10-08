<?php

use wcf\system\WCF;

$styleVariables = [
    ['pageHeaderLayout', 'logoTop', null],
    ['wcfHeaderMenuLinkBackgroundActive', 'rgba(36, 66, 95, 1)', 'rgba(54, 72, 96, 1)'],
    ['wcfHeaderMenuDropdownBackgroundActive', 'rgba(65, 121, 173, 1)', 'rgba(63, 82, 112, 1)'],
    ['wcfHeaderSearchBoxPlaceholder', 'rgba(218, 218, 218, 1)', 'rgba(207, 207, 207, 1)'],
    ['wcfHeaderSearchBoxPlaceholderActive', 'rgba(218, 218, 218, 1)', 'rgba(207, 207, 207, 1)'],
    ['wcfContentDimmedText', 'rgba(106, 110, 115, 1)', 'rgba(139, 141, 144, 1)'],
];

$sql = "INSERT INTO             wcf1_style_variable
                                (variableName, defaultValue, defaultValueDarkMode)
        VALUES                  (?, ?, ?)
        ON DUPLICATE KEY UPDATE defaultValue = VALUES(defaultValue),
                                defaultValueDarkMode = VALUES(defaultValueDarkMode)";
$statement = WCF::getDB()->prepare($sql);

foreach ($styleVariables as $data) {
    [$variableName, $defaultValue, $defaultValueDarkMode] = $data;

    $statement->execute([
        $variableName,
        $defaultValue,
        $defaultValueDarkMode,
    ]);
}

// Existing styles keep the classic page header, only an explicit value switches
// them to the new one. `IFNULL()` preserves values that are already set.
$sql = "INSERT INTO             wcf1_style_variable_value
                                (styleID, variableID, variableValue)
        SELECT                  style.styleID, variable.variableID, ?
        FROM                    wcf1_style style
        CROSS JOIN              wcf1_style_variable variable
        WHERE                   variable.variableName = ?
        ON DUPLICATE KEY UPDATE variableValue = IFNULL(variableValue, VALUES(variableValue))";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    'classic',
    'pageHeaderLayout',
]);
