<?php

/**
 * Normalizes the filenames in the package installation file log during the update from 5.4 to 5.5.
 *
 * @author Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;
use wcf\util\FileUtil;

$sql = "SELECT  *
        FROM    wcf1_package_installation_file_log
        WHERE   filename LIKE ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['./%']);

$sql = "DELETE FROM wcf1_package_installation_file_log
        WHERE       packageID = ?
                AND filename = ?
                AND application = ?";
$deleteStatement = WCF::getDB()->prepare($sql);

$sql = "INSERT IGNORE INTO  wcf1_package_installation_file_log
                            (packageID, filename, application)
        VALUES              (?, ?, ?)";
$insertStatement = WCF::getDB()->prepare($sql);

while ($row = $statement->fetchArray()) {
    $deleteStatement->execute([
        $row['packageID'],
        $row['filename'],
        $row['application'],
    ]);
    $insertStatement->execute([
        $row['packageID'],
        FileUtil::getRealPath($row['filename']),
        $row['application'],
    ]);
}
