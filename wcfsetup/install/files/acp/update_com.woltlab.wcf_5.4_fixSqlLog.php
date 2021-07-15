<?php

/**
 * Adds the missing SQL log entry for wcf1_acp_session_virtual. It might be missing
 * in communities that were upgraded from 2.1 to 3.0.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$packageID = 1;
$sqlTable = "wcf" . WCF_N . "_acp_session_virtual";
$sqlColumn = '';
$sqlIndex = '';

$sql = "SELECT  isDone
        FROM    wcf" . WCF_N . "_package_installation_sql_log
        WHERE   packageID = ?
            AND sqlTable = ?
            AND sqlColumn = ?
            AND sqlIndex = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
    $packageID,
    $sqlTable,
    $sqlColumn,
    $sqlIndex,
]);

$isDone = $statement->fetchSingleColumn();

if ($isDone === false) {
    // Create the record of no row can be found.
    $sql = "INSERT INTO wcf" . WCF_N . "_package_installation_sql_log
                (packageID, sqlTable, sqlColumn, sqlIndex, isDone)
            VALUES
                (?, ?, ?, ?, ?)";
    $statement = WCF::getDB()->prepareStatement($sql);
    $statement->execute([
        $packageID,
        $sqlTable,
        $sqlColumn,
        $sqlIndex,
        1,
    ]);
} else {
    if ($isDone) {
        // The record exists with isDone = 1. We don't need to do anything.
    } else {
        // Mark the record as done, because the table must exist for the ACP to work.
        $sql = "UPDATE  wcf" . WCF_N . "_package_installation_sql_log
                SET     isDone = ?
                WHERE   packageID = ?
                    AND sqlTable = ?
                    AND sqlColumn = ?
                    AND sqlIndex = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            1,
            $packageID,
            $sqlTable,
            $sqlColumn,
            $sqlIndex,
        ]);
    }
}
