<?php

/**
 * Migrates the title of user ranks from the `rankTitle` column of
 * `wcf1_user_rank` (literal value or language variable) into the
 * `wcf1_user_rank_l10n` table.
 *
 * The user ranks shipped with the package (`wcf.user.rank.administrator`,
 * `wcf.user.rank.moderator` and `wcf.user.rank.user0` to
 * `wcf.user.rank.user5`) are linked to their language variable via
 * `l10nIdentifier`; their localized values are stored as pristine copies and
 * kept in sync with the phrases, which remain in place. User ranks created or
 * edited by an administrator own their title: they stay unlinked and their
 * obsolete `wcf.user.rank.userRank<id>` phrases are removed, together with the
 * phrases left behind by previously deleted user ranks.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (adding the `l10nIdentifier` column and creating the
 *   `wcf1_user_rank_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `rankTitle` column) must run AFTER this script.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\user\rank\UserRank;
use wcf\system\cache\builder\UserRankCacheBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

$shippedLanguageItems = [
    'wcf.user.rank.administrator',
    'wcf.user.rank.moderator',
    'wcf.user.rank.user0',
    'wcf.user.rank.user1',
    'wcf.user.rank.user2',
    'wcf.user.rank.user3',
    'wcf.user.rank.user4',
    'wcf.user.rank.user5',
];

// Legacy `UserRank::getTitle()` resolved any existing phrase, which includes
// the phrases of other user ranks and phrases named by other packages.
$sql = "SELECT  DISTINCT languageItem
        FROM    wcf1_language_item
        WHERE   languageItem IN (
                    SELECT  rankTitle
                    FROM    wcf1_user_rank
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_user_rank_l10n")->execute();

// Link the shipped user ranks to their language variable; user ranks created
// by an administrator own their title and stay unlinked.
$statement = WCF::getDB()->prepare("SELECT rankID, rankTitle FROM wcf1_user_rank");
$statement->execute();
$updateStatement = WCF::getDB()->prepare(
    "UPDATE wcf1_user_rank SET l10nIdentifier = ? WHERE rankID = ?"
);
while ($row = $statement->fetchArray()) {
    $updateStatement->execute([
        \in_array($row['rankTitle'], $shippedLanguageItems, true) ? $row['rankTitle'] : null,
        $row['rankID'],
    ]);
}

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    UserRank::getL10nDefinition(),
    static function (array $row) use ($existingLanguageItems): array {
        $isLanguageItem = \in_array($row['rankTitle'], $existingLanguageItems, true);

        return [
            'sources' => [
                'rankTitle' => new L10nLanguageItemSource(
                    languageItem: $isLanguageItem ? $row['rankTitle'] : null,
                    literal: $row['rankTitle'],
                ),
            ],
        ];
    }
);

// Cached user ranks were created without their localized values. Reset
// before the phrases are removed, because a failure after the removal makes a
// retry migrate the phrase names as literals.
UserRankCacheBuilder::getInstance()->reset();

// Removes the migrated phrases as well as those the legacy code left behind:
// deleting a user group removed its user ranks without their phrases, and
// before 6.2 the phrases were saved in the `wcf.user` category, which the
// legacy cleanup did not cover.
$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.user\.rank\.userRank[0-9]+$']);
if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
