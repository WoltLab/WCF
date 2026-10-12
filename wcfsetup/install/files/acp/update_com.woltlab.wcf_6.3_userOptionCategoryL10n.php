<?php

/**
 * Migrates the title of user option categories from the
 * `wcf.user.option.category.<categoryName>` language variables into the
 * `wcf1_user_option_category_l10n` table.
 *
 * System categories (shipped by a package) are linked to their language
 * variable via `l10nIdentifier`; their localized values are stored as pristine
 * copies and kept in sync with the phrases, which remain in place. Categories
 * without a language variable, e.g. imported ones, fall back to their name.
 * Categories created by an administrator (`category<id>`) own their localized
 * value: they stay unlinked and their obsolete phrases are removed.
 *
 * IMPORTANT ordering constraint for package.xml: The database script
 * `acp/database/update_com.woltlab.wcf_6.3_step1.php` (adding the
 * `l10nIdentifier` column and creating the `wcf1_user_option_category_l10n`
 * table) must run BEFORE this script.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\user\option\category\UserOptionCategory;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\WCF;

$isAdminCreated = static fn(string $categoryName): bool => (bool)\preg_match('/^category\d+$/', $categoryName);

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_user_option_category_l10n")->execute();

// Link system categories to their language variable; administrator created
// categories own their localized value and stay unlinked.
$statement = WCF::getDB()->prepare("SELECT categoryID, categoryName FROM wcf1_user_option_category");
$statement->execute();
$updateStatement = WCF::getDB()->prepare(
    "UPDATE wcf1_user_option_category SET l10nIdentifier = ? WHERE categoryID = ?"
);
while ($row = $statement->fetchArray()) {
    $updateStatement->execute([
        $isAdminCreated($row['categoryName']) ? null : 'wcf.user.option.category.' . $row['categoryName'],
        $row['categoryID'],
    ]);
}

// Migrate the phrase values into the l10n storage.
L10nLanguageItemSync::migrate(
    UserOptionCategory::getL10nDefinition(),
    static function (array $row) use ($isAdminCreated): array {
        return [
            'sources' => [
                'title' => new L10nLanguageItemSource(
                    languageItem: 'wcf.user.option.category.' . $row['categoryName'],
                    literal: $row['categoryName'],
                    deleteAfterMigration: $isAdminCreated($row['categoryName']),
                ),
            ],
        ];
    }
);

UserOptionCacheBuilder::getInstance()->reset();
