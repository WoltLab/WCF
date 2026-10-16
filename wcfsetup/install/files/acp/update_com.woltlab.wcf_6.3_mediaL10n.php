<?php

/**
 * Migrates the localized title, caption and alternative text of media files
 * from `wcf1_media_content` into the `wcf1_media_l10n` table.
 *
 * Monolingual media files (`isMultilingual = 0`) were displayed with the
 * content of their `languageID` in every language, which equals a monolingual
 * row (`languageID = NULL`). Multilingual media files keep their per-language
 * rows.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_media_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `wcf1_media_content` table) must run AFTER this script.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_media_l10n")->execute();

$sql = "INSERT INTO wcf1_media_l10n
                    (mediaID, languageID, title, caption, altText)
        SELECT      media_content.mediaID, media_content.languageID,
                    media_content.title, media_content.caption, media_content.altText
        FROM        wcf1_media_content media_content
        INNER JOIN  wcf1_media media
        ON          media.mediaID = media_content.mediaID
        WHERE       media.isMultilingual = ?
                AND media_content.languageID IS NOT NULL";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([1]);

$defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();

$sql = "SELECT      media_content.*, media.languageID AS mediaLanguageID
        FROM        wcf1_media_content media_content
        INNER JOIN  wcf1_media media
        ON          media.mediaID = media_content.mediaID
        WHERE       media.isMultilingual = ?
                AND media_content.mediaID > ?
        ORDER BY    media_content.mediaID";
$selectStatement = WCF::getDB()->prepare($sql, 1_000);

$sql = "INSERT INTO wcf1_media_l10n
                    (mediaID, languageID, title, caption, altText)
        VALUES      (?, NULL, ?, ?, ?)";
$insertStatement = WCF::getDB()->prepare($sql);

/**
 * Picks the content row that was displayed for a monolingual media file:
 * its own language, a row without a language, the default language and
 * finally the lowest language id.
 *
 * @param list<array<string, mixed>> $rows
 * @return array<string, mixed>
 */
$pickRow = static function (array $rows) use ($defaultLanguageID): array {
    $mediaLanguageID = $rows[0]['mediaLanguageID'];

    $preferred = [];
    foreach ($rows as $row) {
        $preferred[] = match (true) {
            $mediaLanguageID !== null && $row['languageID'] === $mediaLanguageID => -3,
            $row['languageID'] === null => -2,
            $row['languageID'] === $defaultLanguageID => -1,
            default => $row['languageID'],
        };
    }

    return $rows[\array_search(\min($preferred), $preferred, true)];
};

$lastMediaID = 0;
do {
    $selectStatement->execute([0, $lastMediaID]);

    $rowsByMedia = [];
    while ($row = $selectStatement->fetchArray()) {
        $rowsByMedia[$row['mediaID']][] = $row;
    }

    if ($rowsByMedia === []) {
        break;
    }

    // The last media file may be incomplete due to the limit, it is read
    // again in the next iteration unless it is the only one.
    $mediaIDs = \array_keys($rowsByMedia);
    if (\count($mediaIDs) > 1) {
        unset($rowsByMedia[\end($mediaIDs)]);
    }

    WCF::getDB()->beginTransaction();
    foreach ($rowsByMedia as $mediaID => $rows) {
        $row = $pickRow($rows);

        $insertStatement->execute([
            $mediaID,
            $row['title'],
            $row['caption'],
            $row['altText'],
        ]);

        $lastMediaID = $mediaID;
    }
    WCF::getDB()->commitTransaction();
} while (true);
