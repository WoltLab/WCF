<?php

/**
 * Removes obsolete files.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\package\PackageCache;
use wcf\system\WCF;

$files = [
    'lib/acp/form/UserGroupPromoteOwnerForm.class.php',
    'lib/system/database/table/DatabaseTableUtil.class.php',
];

$sql = "SELECT  packageID
        FROM    wcf" . WCF_N . "_package_installation_file_log
        WHERE   filename = ?";
$searchStatement = WCF::getDB()->prepareStatement($sql);

$sql = "DELETE FROM wcf" . WCF_N . "_package_installation_file_log
        WHERE       packageID = ?
                AND filename = ?";
$deletionStatement = WCF::getDB()->prepareStatement($sql);

$packageID = $this->installation->getPackageID();

foreach ($files as $file) {
    $searchStatement->execute([$file]);
    $filePackageID = $searchStatement->fetchSingleColumn();
    if ($filePackageID !== false && $filePackageID != $packageID) {
        throw new \UnexpectedValueException("File '{$file}' does not belong to package '{$this->installation->getPackage()->package}' but to package '" . PackageCache::getInstance()->getPackage($filePackageID)->package . "'.");
    }

    if (\file_exists(WCF_DIR . $file)) {
        \unlink(WCF_DIR . $file);
    }

    $deletionStatement->execute([
        $packageID,
        $file,
    ]);
}
