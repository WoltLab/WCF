<?php

/**
 * Migrates the title of label groups from the `groupName` column of
 * `wcf1_label_group` (literal value or language variable) into the
 * `wcf1_label_group_l10n` table.
 *
 * Label groups own their title, the obsolete `wcf.acp.label.group<id>` phrases
 * are removed, together with the phrases that older versions left behind when
 * a label group was deleted.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_label_group_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `groupName` column) must run AFTER this script.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\label\group\LabelGroup;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

// Legacy `LabelGroup::getTitle()` resolved any existing phrase, which includes
// the phrases of other label groups and phrases named by other packages.
$sql = "SELECT  DISTINCT languageItem
        FROM    wcf1_language_item
        WHERE   languageItem IN (
                    SELECT  groupName
                    FROM    wcf1_label_group
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_label_group_l10n")->execute();

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    LabelGroup::getL10nDefinition(),
    static function (array $row) use ($existingLanguageItems): array {
        $isLanguageItem = \in_array($row['groupName'], $existingLanguageItems, true);

        return [
            'sources' => [
                'groupName' => new L10nLanguageItemSource(
                    languageItem: $isLanguageItem ? $row['groupName'] : null,
                    literal: $row['groupName'],
                ),
            ],
        ];
    }
);

// Cached label groups were created without their localized values. Reset
// before the phrases are removed, because a failure after the removal makes a
// retry migrate the phrase names as literals.
LabelCacheBuilder::getInstance()->reset();

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.acp\.label\.group[0-9]+$']);
if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
