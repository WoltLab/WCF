<?php

/**
 * Updates icon-based trophies to the FA 6 names.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\system\style\exception\UnknownIcon;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;

WCF::getDB()->beginTransaction();

$sql = "SELECT  trophyID, iconName
        FROM    wcf1_trophy
        WHERE   type = ?
            AND iconName IS NOT NULL
            AND iconName <> ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    Trophy::TYPE_BADGE,
    '[]',
]);
$iconData = $statement->fetchMap('trophyID', 'iconName');

$sql = "UPDATE  wcf1_trophy
        SET     iconName = ?
        WHERE   trophyID = ?";
$statement = WCF::getDB()->prepare($sql);
foreach ($iconData as $trophyID => $oldIconName) {
    // No modification if the icon already contains a semicolon
    // (implying it already was migrated).
    if (\str_contains($oldIconName, ';')) {
        continue;
    }

    try {
        $newIconName = FontAwesomeIcon::mapVersion4($oldIconName);
    } catch (UnknownIcon $e) {
        // If the old icon is unknown we replace it with a placeholder.
        \wcf\functions\exception\logThrowable($e);

        $newIconName = FontAwesomeIcon::fromString('trophy')->__toString();
    }

    $statement->execute([
        $newIconName,
        $trophyID,
    ]);
}

WCF::getDB()->commitTransaction();

TrophyCache::getInstance()->clearCache();
