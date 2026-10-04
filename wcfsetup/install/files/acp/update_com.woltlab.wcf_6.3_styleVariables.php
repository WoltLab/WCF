<?php

use wcf\system\WCF;

$styleVariables = [
    ['pageHeaderLayout', 'classic', null],
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
