<?php

/**
 * Migrates the title of reaction types from the `title` column of
 * `wcf1_reaction_type` (literal value or `wcf.reactionType.title<id>` language
 * variable) into the `wcf1_reaction_type_l10n` table.
 *
 * The reaction types shipped with the package (`wcf.reactionType.title1` to
 * `wcf.reactionType.title5`) are linked to their language variable via
 * `l10nIdentifier`; their localized values are stored as pristine copies and
 * kept in sync with the phrases. Reaction types created by an administrator
 * own their title: they stay unlinked and their obsolete phrases are removed,
 * together with the phrases left behind by previously deleted reaction types.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (adding the `l10nIdentifier` column and creating the
 *   `wcf1_reaction_type_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `title` column) must run AFTER this script.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\reaction\type\ReactionType;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

$shippedLanguageItems = [
    'wcf.reactionType.title1',
    'wcf.reactionType.title2',
    'wcf.reactionType.title3',
    'wcf.reactionType.title4',
    'wcf.reactionType.title5',
];

// Legacy `ReactionType::getTitle()` resolved any existing phrase, which
// includes phrases named by other packages.
$sql = "SELECT  DISTINCT languageItem
        FROM    wcf1_language_item
        WHERE   languageItem IN (
                    SELECT  title
                    FROM    wcf1_reaction_type
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

$getOwnLanguageItem = static function (array $row): ?string {
    $languageItem = 'wcf.reactionType.title' . $row['reactionTypeID'];

    // `wcf.reactionType.title` is a placeholder written by the legacy create
    // action before the phrase of the new reaction type was saved.
    if ($row['title'] === $languageItem || $row['title'] === 'wcf.reactionType.title') {
        return $languageItem;
    }

    return null;
};

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_reaction_type_l10n")->execute();

// Link the shipped reaction types to their language variable; reaction types
// created by an administrator own their title and stay unlinked.
$statement = WCF::getDB()->prepare("SELECT reactionTypeID, title FROM wcf1_reaction_type");
$statement->execute();
$updateStatement = WCF::getDB()->prepare(
    "UPDATE wcf1_reaction_type SET l10nIdentifier = ? WHERE reactionTypeID = ?"
);
while ($row = $statement->fetchArray()) {
    $languageItem = $getOwnLanguageItem($row);

    $updateStatement->execute([
        \in_array($languageItem, $shippedLanguageItems, true) ? $languageItem : null,
        $row['reactionTypeID'],
    ]);
}

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    ReactionType::getL10nDefinition(),
    static function (array $row) use ($getOwnLanguageItem, $existingLanguageItems): array {
        $languageItem = $getOwnLanguageItem($row);
        if ($languageItem === null && \in_array($row['title'], $existingLanguageItems, true)) {
            $languageItem = $row['title'];
        }

        return [
            'sources' => [
                'title' => new L10nLanguageItemSource(
                    languageItem: $languageItem,
                    literal: $row['title'],
                ),
            ],
        ];
    }
);

// Removes the migrated phrases as well as those the legacy code left behind:
// the delete action never removed the phrase of a reaction type, and switching
// a title to a monolingual value kept the previous phrase.
$conditions = new PreparedStatementConditionBuilder();
$conditions->add('languageItem REGEXP ?', ['^wcf\.reactionType\.title[0-9]+$']);
$conditions->add('languageItem NOT IN (?)', [$shippedLanguageItems]);
$sql = "DELETE FROM wcf1_language_item
        {$conditions}";
$statement = WCF::getDB()->prepare($sql);
$statement->execute($conditions->getParameters());
if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}

// Cached reaction types were created without their localized values.
ReactionTypeCacheBuilder::getInstance()->reset();
