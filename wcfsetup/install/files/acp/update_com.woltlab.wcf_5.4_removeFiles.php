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
    'acp/pre_update_com.woltlab.wcf_2.1.php',
    'acp/update_com.woltlab.wcf_3.0.14.php',
    'acp/update_com.woltlab.wcf_3.0_noop.php',
    'acp/update_com.woltlab.wcf_3.0_pre_sql.php',
    'acp/update_com.woltlab.wcf_3.1.14.php',
    'acp/update_com.woltlab.wcf_3.1.2.php',
    'acp/update_com.woltlab.wcf_3.1_addColumn.php',
    'acp/update_com.woltlab.wcf_3.1_preUpdate.php',
    'acp/update_com.woltlab.wcf_5.2.10_orphanedComments.php',
    'acp/update_com.woltlab.wcf_5.2.php',
    'acp/update_com.woltlab.wcf_5.2_deleteRecentActivity.php',
    'acp/update_com.woltlab.wcf_5.2_prePhpApi.php',
    'acp/update_com.woltlab.wcf_5.2_preUpdate.php',
    'acp/update_com.woltlab.wcf_5.2_reactionUpdate.php',
    'acp/update_com.woltlab.wcf_5.2_reloadOptions.php',
    'acp/update_com.woltlab.wcf_5.3.1_style.php',
    'acp/update_com.woltlab.wcf_5.3.php',
    'acp/update_com.woltlab.wcf_5.3_packageServer.php',
    'acp/update_com.woltlab.wcf_5.3_preUpdate.php',
    'lib/acp/form/UserGroupPromoteOwnerForm.class.php',
    'lib/acp/page/ApplicationManagementPage.class.php',
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
