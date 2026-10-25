<?php

use wcf\system\WCF;

// The protection of a style can no longer be lifted. Styles without a package
// name are never updated, so their protection would only lock the preset
// declarations. This affects copies created before 6.3 and imported styles.
$sql = "UPDATE      wcf1_style_variable_value style_variable_value
        INNER JOIN  wcf1_style_variable style_variable
        ON          style_variable.variableID = style_variable_value.variableID
        INNER JOIN  wcf1_style style
        ON          style.styleID = style_variable_value.styleID
        SET         style_variable_value.variableValue = REPLACE(style_variable_value.variableValue, ?, '')
        WHERE       style.isTainted = ?
                AND style.packageName = ?
                AND style_variable.variableName IN (?, ?, ?)";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    "/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n",
    0,
    '',
    'individualScss',
    'individualScssDarkMode',
    'overrideScss',
]);

$sql = "UPDATE  wcf1_style
        SET     isTainted = ?
        WHERE   isTainted = ?
            AND packageName = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([1, 0, '']);
