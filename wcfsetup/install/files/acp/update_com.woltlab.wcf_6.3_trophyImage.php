<?php

/**
 * Migrates the images of image trophies from `images/trophy/` to the file system.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The `objectType` PIP (registering `com.woltlab.wcf.trophy.image`) must run
 *   BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `iconFile` column) must run AFTER this script.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\trophy\Trophy;
use wcf\system\cache\builder\TrophyCacheBuilder;
use wcf\system\WCF;

$sql = "SELECT  trophyID, iconFile
        FROM    wcf1_trophy
        WHERE   type = ?
            AND iconFile <> ?
            AND iconFileID IS NULL";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    Trophy::TYPE_IMAGE,
    '',
]);
$trophyIDsByImage = [];
while ($row = $statement->fetchArray()) {
    $trophyIDsByImage[$row['iconFile']][] = $row['trophyID'];
}

if ($trophyIDsByImage === []) {
    return;
}

$imageDirectory = \realpath(\WCF_DIR . 'images/trophy/');
if ($imageDirectory === false) {
    return;
}

$sql = "UPDATE  wcf1_trophy
        SET     iconFileID = ?
        WHERE   trophyID = ?";
$statement = WCF::getDB()->prepare($sql);

foreach ($trophyIDsByImage as $iconFile => $trophyIDs) {
    $pathname = \realpath(\WCF_DIR . 'images/trophy/' . $iconFile);
    if ($pathname === false || !\str_starts_with($pathname, $imageDirectory . \DIRECTORY_SEPARATOR)) {
        continue;
    }

    // Every trophy must own its file because the file is deleted together with
    // the trophy. The last trophy takes over the original, all others receive
    // a copy.
    $lastTrophyID = \array_pop($trophyIDs);
    foreach ($trophyIDs as $trophyID) {
        $file = new CreateFileFromExistingFile(
            $pathname,
            \basename($pathname),
            'com.woltlab.wcf.trophy.image',
            copy: true,
        )();
        if ($file !== null) {
            $statement->execute([$file->fileID, $trophyID]);
        }
    }

    $file = new CreateFileFromExistingFile(
        $pathname,
        \basename($pathname),
        'com.woltlab.wcf.trophy.image',
    )();
    if ($file !== null) {
        $statement->execute([$file->fileID, $lastTrophyID]);
    }
}

TrophyCacheBuilder::getInstance()->reset();
TrophyCacheBuilder::getInstance()->reset(['onlyEnabled' => 1]);
