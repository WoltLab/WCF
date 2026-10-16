<?php

/**
 * Migrates the custom description of awarded trophies from the `description`
 * column of `wcf1_user_trophy` (literal value or language variable) into the
 * `wcf1_user_trophy_l10n` table.
 *
 * The obsolete `wcf.user.trophy.userTrophy.description<id>` phrases (created
 * by the ACP form) and `wcf.user.trophy.description<id>` phrases (created by
 * the importer) are removed, including those left behind by previously deleted
 * user trophies.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_user_trophy_l10n` table) must run BEFORE this script.
 * - The script `acp/update_com.woltlab.wcf_6.3_trophyL10n.php` must run BEFORE
 *   this script, because the trophies use the same `wcf.user.trophy.description<id>`
 *   pattern for their own descriptions.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `description` column) must run AFTER this script.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\user\trophy\UserTrophy;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_user_trophy_l10n")->execute();

// Legacy `UserTrophy::getDescription()` resolved any existing phrase, which
// includes phrases named by other packages.
$sql = "SELECT  DISTINCT languageItem
        FROM    wcf1_language_item
        WHERE   languageItem IN (
                    SELECT  description
                    FROM    wcf1_user_trophy
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

// Trophies that are awarded automatically never have a description, reading
// only the rows with a description keeps large tables out of memory.
$conditions = new PreparedStatementConditionBuilder();
$conditions->add("description <> ''");

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    UserTrophy::getL10nDefinition(),
    static function (array $row) use ($existingLanguageItems): array {
        $description = (string)$row['description'];

        return [
            'sources' => [
                'description' => new L10nLanguageItemSource(
                    languageItem: \in_array($description, $existingLanguageItems, true) ? $description : null,
                    literal: $description,
                ),
            ],
        ];
    },
    $conditions
);

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.user\.trophy\.(userTrophy\.)?description[0-9]+$']);

if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
