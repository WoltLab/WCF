<?php

use wcf\system\WCF;

// The Core style ships no colors of its own, its authored values are the
// defaults. This must run after the defaults are updated, which makes the
// colors introduced with 6.3 available as the authored values.
$sql = "INSERT INTO             wcf1_style_variable_value
                                (styleID, variableID, authoredValue, authoredValueDarkMode)
        SELECT                  style.styleID, variable.variableID, variable.defaultValue, variable.defaultValueDarkMode
        FROM                    wcf1_style style
        CROSS JOIN              wcf1_style_variable variable
        WHERE                   style.packageName = ?
        ON DUPLICATE KEY UPDATE authoredValue = VALUES(authoredValue),
                                authoredValueDarkMode = VALUES(authoredValueDarkMode)";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['com.woltlab.wcf']);
