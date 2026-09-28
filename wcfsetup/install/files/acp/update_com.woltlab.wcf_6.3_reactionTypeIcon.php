<?php

/**
 * Migrates the reaction type icons from `images/reaction/` to the file system.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\command\file\CreateFileFromExistingFile;
use wcf\system\WCF;

$sql = "SELECT  reactionTypeID, iconFile
        FROM    wcf1_reaction_type
        WHERE   iconFile <> ?
            AND iconFileID IS NULL";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['']);
$reactionTypeIDsByIcon = [];
while ($row = $statement->fetchArray()) {
    $reactionTypeIDsByIcon[$row['iconFile']][] = $row['reactionTypeID'];
}

if ($reactionTypeIDsByIcon === []) {
    return;
}

$imageDirectory = \realpath(\WCF_DIR . 'images/reaction/');
if ($imageDirectory === false) {
    return;
}

// The bundled images are owned by the package and must stay in place.
$bundledImages = [
    'confused.svg',
    'haha.svg',
    'like.svg',
    'sad.svg',
    'thanks.svg',
    'thumbsDown.svg',
    'thumbsUp.svg',
];

$sql = "UPDATE  wcf1_reaction_type
        SET     iconFileID = ?
        WHERE   reactionTypeID = ?";
$statement = WCF::getDB()->prepare($sql);

foreach ($reactionTypeIDsByIcon as $iconFile => $reactionTypeIDs) {
    $pathname = \realpath(\WCF_DIR . 'images/reaction/' . $iconFile);
    if ($pathname === false || !\str_starts_with($pathname, $imageDirectory . \DIRECTORY_SEPARATOR)) {
        continue;
    }

    // Reaction types can share a single image, but every reaction type must
    // own its file because the file is deleted together with the reaction
    // type. The last reaction type takes over the original, all others
    // receive a copy.
    $lastReactionTypeID = \in_array($iconFile, $bundledImages, true) ? null : \array_pop($reactionTypeIDs);
    foreach ($reactionTypeIDs as $reactionTypeID) {
        $file = new CreateFileFromExistingFile(
            $pathname,
            \basename($pathname),
            'com.woltlab.wcf.reactionType.icon',
            copy: true,
        )();
        if ($file !== null) {
            $statement->execute([$file->fileID, $reactionTypeID]);
        }
    }

    if ($lastReactionTypeID !== null) {
        $file = new CreateFileFromExistingFile(
            $pathname,
            \basename($pathname),
            'com.woltlab.wcf.reactionType.icon',
        )();
        if ($file !== null) {
            $statement->execute([$file->fileID, $lastReactionTypeID]);
        }
    }
}
