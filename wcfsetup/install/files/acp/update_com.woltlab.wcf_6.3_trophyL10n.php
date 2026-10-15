<?php

/**
 * Migrates the title and the description of trophies from the `title` and
 * `description` columns of `wcf1_trophy` (literal value or language variable)
 * into the `wcf1_trophy_l10n` table.
 *
 * The obsolete `wcf.user.trophy.title<id>` and
 * `wcf.user.trophy.description<id>` phrases are removed, including those left
 * behind by previously deleted trophies. Imported descriptions of awarded
 * trophies use the same pattern (`wcf.user.trophy.description<userTrophyID>`)
 * and are kept.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_trophy_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `title` and `description` columns) must run AFTER this
 *   script.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\trophy\Trophy;
use wcf\system\cache\builder\TrophyCacheBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_trophy_l10n")->execute();

// Legacy `Trophy::getTitle()` and `Trophy::getDescription()` resolved any
// existing phrase, which includes phrases named by other packages.
$getExistingLanguageItems = static function (string $columnName): array {
    $sql = "SELECT  DISTINCT languageItem
            FROM    wcf1_language_item
            WHERE   languageItem IN (
                        SELECT  {$columnName}
                        FROM    wcf1_trophy
                    )";
    $statement = WCF::getDB()->prepare($sql);
    $statement->execute();

    return $statement->fetchAll(\PDO::FETCH_COLUMN);
};
$existingTitleLanguageItems = $getExistingLanguageItems('title');
$existingDescriptionLanguageItems = $getExistingLanguageItems('description');

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    Trophy::getL10nDefinition(),
    static function (array $row) use ($existingTitleLanguageItems, $existingDescriptionLanguageItems): array {
        $title = (string)($row['title'] ?? '');
        $description = (string)($row['description'] ?? '');

        return [
            'sources' => [
                'title' => new L10nLanguageItemSource(
                    languageItem: \in_array($title, $existingTitleLanguageItems, true) ? $title : null,
                    literal: $title,
                ),
                'description' => new L10nLanguageItemSource(
                    languageItem: \in_array($description, $existingDescriptionLanguageItems, true) ? $description : null,
                    literal: $description,
                ),
            ],
        ];
    }
);

// Cached trophies were created without their localized values. Reset before
// the phrases are removed, because a failure after the removal makes a retry
// migrate the phrase names as literals.
TrophyCacheBuilder::getInstance()->reset();
TrophyCacheBuilder::getInstance()->reset(['onlyEnabled' => 1]);

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.user\.trophy\.title[0-9]+$']);
$deletedPhrases = $statement->getAffectedRows();

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?
                AND languageItem NOT IN (
                        SELECT  description
                        FROM    wcf1_user_trophy
                        WHERE   description IS NOT NULL
                    )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.user\.trophy\.description[0-9]+$']);
$deletedPhrases += $statement->getAffectedRows();

if ($deletedPhrases > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
