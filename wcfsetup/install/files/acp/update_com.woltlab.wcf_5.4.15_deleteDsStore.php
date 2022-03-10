<?php

/**
 * Deletes .DS_Store and ._.DS_Store files.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\application\Application;
use wcf\system\WCF;

$sql = "SELECT  *
        FROM    wcf1_package_installation_file_log
        WHERE   filename LIKE ?
            OR  filename LIKE ?";
$selectStatement = WCF::getDB()->prepare($sql);
$selectStatement->execute([
    '%/.DS_Store',
    '%/._.DS_Store',
]);

$sql = "DELETE FROM wcf1_package_installation_file_log
        WHERE       packageID = ?
                AND filename = ?
                AND application = ?";
$deleteStatement = WCF::getDB()->prepare($sql);

while (($row = $selectStatement->fetchArray())) {
    $packageDir = Application::getDirectory($row['application']);
    $fullPath = $packageDir . $row['filename'];

    if (!\file_exists($fullPath) || !\is_file($fullPath)) {
        continue;
    }

    if (
        \basename($fullPath) !== '.DS_Store'
        && \basename($fullPath) !== '._.DS_Store'
    ) {
        continue;
    }

    \unlink($fullPath);
    $deleteStatement->execute([
        $row['packageID'],
        $row['filename'],
        $row['application'],
    ]);
}
