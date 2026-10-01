<?php

/**
 * Migrates the description of cronjobs from the `description` column of
 * `wcf1_cronjob` (literal value or language variable) into the
 * `wcf1_cronjob_l10n` table and removes the obsolete
 * `wcf.acp.cronjob.description.cronjob<id>` phrases, together with the
 * phrases left behind by previously deleted cronjobs.
 *
 * Cronjobs delivered by a package receive their descriptions from the
 * `cronjob` PIP on every later update of that package.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_cronjob_l10n` table) must run BEFORE this script.
 * - The `cronjob` PIP must run AFTER this script: a cronjob created by the
 *   PIP before would be migrated from its empty `description` column, losing
 *   the values written by the PIP.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `description` column) must run AFTER this script.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\cronjob\Cronjob;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

// Legacy `Cronjob::getDescription()` resolved any existing phrase, not only
// the cronjob's own `wcf.acp.cronjob.description.cronjob<id>`.
$sql = "SELECT  DISTINCT languageItem
        FROM    wcf1_language_item
        WHERE   languageItem IN (
                    SELECT  description
                    FROM    wcf1_cronjob
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_cronjob_l10n")->execute();

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    Cronjob::getL10nDefinition(),
    static function (array $row) use ($existingLanguageItems): array {
        $isLanguageItem = \in_array($row['description'], $existingLanguageItems, true);

        return [
            'sources' => [
                'description' => new L10nLanguageItemSource(
                    languageItem: $isLanguageItem ? $row['description'] : null,
                    literal: $row['description'],
                ),
            ],
        ];
    }
);

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.acp\.cronjob\.description\.cronjob[0-9]+$']);
if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
