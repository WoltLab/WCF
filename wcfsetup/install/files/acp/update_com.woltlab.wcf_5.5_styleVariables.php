<?php

/**
 * Inserts new style variables introduced with WoltLab Suite 5.5.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$values = [
    'wcfUserMenuBackground' => 'rgba(255, 255, 255, 1)',
    'wcfUserMenuBackgroundActive' => 'rgba(239, 239, 239, 1)',
    'wcfUserMenuText' => 'rgba(58, 58, 61, 1)',
    'wcfUserMenuTextDimmed', 'rgba(108, 108, 108, 1)',
    'wcfUserMenuIndicator' => 'rgba(49, 138, 220, 1)',
    'wcfUserMenuBorder' => 'rgb(221, 221, 221, 1)',
];

$sql = "INSERT IGNORE INTO wcf1_style_variable (variableName, defaultValue) VALUES (?, ?)";
$statement = WCF::getDB()->prepare($sql);
foreach ($values as $variableName => $defaultValue) {
    $statement->execute([
        $variableName,
        $defaultValue,
    ]);
}
