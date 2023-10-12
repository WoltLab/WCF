<?php

/**
 * Fixes the style variable value of `individualScssDarkMode`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

// Fix the default value for style variables.
$sql = "UPDATE  wcf1_style_variable
        SET     defaultValueDarkMode = ?
        WHERE   variableName = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([null, 'individualScssDarkMode']);

// Fixes any bad values stored for existing styles.
$sql = "UPDATE  wcf1_style_variable_value
        SET     variableValueDarkMode = ?
        WHERE   variableID = (
                    SELECT  variableID
                    FROM    wcf1_style_variable
                    WHERE   variableName = ?
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([null, 'individualScssDarkMode']);
