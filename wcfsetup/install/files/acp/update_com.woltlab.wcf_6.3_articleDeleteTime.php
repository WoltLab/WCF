<?php

/**
 * Initializes the delete time of articles that are already in the trash bin,
 * preventing them from being pruned immediately after the update.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

$sql = "UPDATE  wcf1_article
        SET     deleteTime = ?
        WHERE   isDeleted = ?
            AND deleteTime = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    \TIME_NOW,
    1,
    0,
]);
