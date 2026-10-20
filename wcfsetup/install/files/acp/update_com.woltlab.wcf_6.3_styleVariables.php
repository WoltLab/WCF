<?php

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

$styleVariables = [
    ['pageFooterLayout', 'compact', null],
    ['pageHeaderLayout', 'logoTop', null],
    ['wcfHeaderMenuLinkBackgroundActive', 'rgba(36, 66, 95, 1)', 'rgba(54, 72, 96, 1)'],
    ['wcfHeaderMenuDropdownBackgroundActive', 'rgba(65, 121, 173, 1)', 'rgba(63, 82, 112, 1)'],
    ['wcfHeaderSearchBoxPlaceholder', 'rgba(218, 218, 218, 1)', 'rgba(207, 207, 207, 1)'],
    ['wcfHeaderSearchBoxPlaceholderActive', 'rgba(218, 218, 218, 1)', 'rgba(207, 207, 207, 1)'],
    ['wcfContentDimmedText', 'rgba(106, 110, 115, 1)', 'rgba(139, 141, 144, 1)'],
    ['wcfFooterBackground', 'rgba(242, 242, 242, 1)', 'rgba(33, 36, 40, 1)'],
    ['wcfFooterCopyrightBackground', 'rgba(233, 233, 233, 1)', 'rgba(42, 45, 49, 1)'],
    ['wcfFooterCopyrightLink', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)'],
    ['wcfFooterCopyrightLinkActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)'],
    ['wcfFooterCopyrightText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)'],
    ['wcfFooterHeadlineLink', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)'],
    ['wcfFooterHeadlineLinkActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)'],
    ['wcfFooterHeadlineText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)'],
    ['wcfFooterLink', 'rgba(38, 113, 166, 1)', 'rgba(29, 155, 209, 1)'],
    ['wcfFooterLinkActive', 'rgba(22, 81, 124, 1)', 'rgba(64, 179, 228, 1)'],
    ['wcfFooterText', 'rgba(58, 58, 61, 1)', 'rgba(209, 210, 211, 1)'],
];

// Existing styles keep the footer colors of earlier versions, the new defaults
// only apply to new styles. This must run before the defaults are updated.
$footerColors = [
    'wcfFooterBackground',
    'wcfFooterCopyrightBackground',
    'wcfFooterCopyrightLink',
    'wcfFooterCopyrightLinkActive',
    'wcfFooterCopyrightText',
    'wcfFooterHeadlineLink',
    'wcfFooterHeadlineLinkActive',
    'wcfFooterHeadlineText',
    'wcfFooterLink',
    'wcfFooterLinkActive',
    'wcfFooterText',
];
$conditions = new PreparedStatementConditionBuilder();
$conditions->add('variable.variableName IN (?)', [$footerColors]);
$sql = "INSERT INTO             wcf1_style_variable_value
                                (styleID, variableID, variableValue, variableValueDarkMode)
        SELECT                  style.styleID, variable.variableID, variable.defaultValue, variable.defaultValueDarkMode
        FROM                    wcf1_style style
        CROSS JOIN              wcf1_style_variable variable
        {$conditions}
        ON DUPLICATE KEY UPDATE variableValue = IFNULL(variableValue, VALUES(variableValue)),
                                variableValueDarkMode = IFNULL(variableValueDarkMode, VALUES(variableValueDarkMode))";
$statement = WCF::getDB()->prepare($sql);
$statement->execute($conditions->getParameters());

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

// Existing styles keep the classic page header and footer, only an explicit value
// switches them to the new one. `IFNULL()` preserves values that are already set.
$sql = "INSERT INTO             wcf1_style_variable_value
                                (styleID, variableID, variableValue)
        SELECT                  style.styleID, variable.variableID, ?
        FROM                    wcf1_style style
        CROSS JOIN              wcf1_style_variable variable
        WHERE                   variable.variableName = ?
        ON DUPLICATE KEY UPDATE variableValue = IFNULL(variableValue, VALUES(variableValue))";
$statement = WCF::getDB()->prepare($sql);
foreach (['pageFooterLayout', 'pageHeaderLayout'] as $variableName) {
    $statement->execute([
        'classic',
        $variableName,
    ]);
}
