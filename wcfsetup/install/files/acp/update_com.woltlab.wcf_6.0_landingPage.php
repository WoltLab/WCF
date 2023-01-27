<?php

/**
 * Updates WCF'S landingPageID in wcf1_application from the wcf1_page data.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

$columns = \array_map(
    strtolower(...),
    \array_column(
        WCF::getDB()->getEditor()->getColumns('wcf' . WCF_N . '_page'),
        'name'
    )
);

if (\in_array('islandingpage', $columns)) {
    $sql = "SELECT  pageID
            FROM    wcf1_page
            WHERE   isLandingPage = ?";
    $statement = WCF::getDB()->prepare($sql);
    $statement->execute([
        1,
    ]);
    $landingPageID = $statement->fetchSingleColumn();

    $sql = "UPDATE  wcf1_application
            SET     landingPageID = ?
            WHERE   packageID = ?";
    $statement = WCF::getDB()->prepare($sql);
    $statement->execute([
        $landingPageID ?: NULL,
        1
    ]);
}
