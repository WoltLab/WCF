<?php

/**
 * Fixes the style variable value of `individualScssDarkMode`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

// Follow-up for https://github.com/WoltLab/WCF/commit/ccf4bcc71444a0c75c7543483585706895b51bd8
$sql = "UPDATE  wcf1_style_variable_value
        SET     variableValueDarkMode = ?
        WHERE   variableID = (
                    SELECT  variableID
                    FROM    wcf1_style_variable
                    WHERE   variableName = ?
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([null, 'individualScssDarkMode']);
