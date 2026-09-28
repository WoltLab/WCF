<?php

/**
 * Migrates the rank images from `images/rank/` to the file system.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\user\rank\UserRank;
use wcf\system\WCF;

$sql = "SELECT  rankID, rankImage
        FROM    wcf1_user_rank
        WHERE   rankImage <> ?
            AND rankImageFileID IS NULL";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['']);
$rankIDsByImage = [];
while ($row = $statement->fetchArray()) {
    $rankIDsByImage[$row['rankImage']][] = $row['rankID'];
}

if ($rankIDsByImage === []) {
    return;
}

$imageDirectory = \realpath(\WCF_DIR . UserRank::RANK_IMAGE_DIR);
if ($imageDirectory === false) {
    return;
}

$sql = "UPDATE  wcf1_user_rank
        SET     rankImageFileID = ?
        WHERE   rankID = ?";
$statement = WCF::getDB()->prepare($sql);

foreach ($rankIDsByImage as $rankImage => $rankIDs) {
    $pathname = \realpath(\WCF_DIR . UserRank::RANK_IMAGE_DIR . $rankImage);
    if ($pathname === false || !\str_starts_with($pathname, $imageDirectory . \DIRECTORY_SEPARATOR)) {
        continue;
    }

    // Imported ranks can share a single image, but every rank must own its
    // file because the file is deleted together with the rank. The last rank
    // takes over the original, all others receive a copy.
    $lastRankID = \array_pop($rankIDs);
    foreach ($rankIDs as $rankID) {
        $file = new CreateFileFromExistingFile(
            $pathname,
            \basename($pathname),
            'com.woltlab.wcf.user.rank.image',
            copy: true,
        )();
        if ($file !== null) {
            $statement->execute([$file->fileID, $rankID]);
        }
    }

    $file = new CreateFileFromExistingFile(
        $pathname,
        \basename($pathname),
        'com.woltlab.wcf.user.rank.image',
    )();
    if ($file !== null) {
        $statement->execute([$file->fileID, $lastRankID]);
    }
}
