<?php

/**
 * Migrates the localized title and description of user options from the
 * `wcf.user.option.<optionName>[.description]` language variables into the
 * `wcf1_user_option_l10n` table.
 *
 * System options (shipped by a package) are linked to their language variable
 * via `l10nIdentifier`; their localized values are stored as pristine copies
 * and kept in sync with the phrases. Options created by an administrator
 * (`option<id>`) own their localized value: they stay unlinked and their
 * obsolete phrases are removed.
 *
 * IMPORTANT ordering constraint for package.xml: The database script
 * `acp/database/update_com.woltlab.wcf_6.3_userOption.php` (adding the
 * `l10nIdentifier` column and creating the `wcf1_user_option_l10n` table) must
 * run BEFORE this script.
 */

use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\WCF;

$isAdminCreated = static fn(string $optionName): bool => (bool)\preg_match('/^option\d+$/', $optionName);

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_user_option_l10n")->execute();

// Link system options to their language variable; administrator created
// options own their localized value and stay unlinked.
$statement = WCF::getDB()->prepare("SELECT optionID, optionName FROM wcf1_user_option");
$statement->execute();
$updateStatement = WCF::getDB()->prepare(
    "UPDATE wcf1_user_option SET l10nIdentifier = ? WHERE optionID = ?"
);
while ($row = $statement->fetchArray()) {
    $updateStatement->execute([
        $isAdminCreated($row['optionName']) ? null : 'wcf.user.option.' . $row['optionName'],
        $row['optionID'],
    ]);
}

// Migrate the phrase values into the l10n storage.
L10nLanguageItemSync::migrate(
    UserOption::getL10nDefinition(),
    static function (array $row) use ($isAdminCreated): array {
        $adminCreated = $isAdminCreated($row['optionName']);
        $identifier = 'wcf.user.option.' . $row['optionName'];

        return [
            'sources' => [
                'title' => new L10nLanguageItemSource(
                    languageItem: $identifier,
                    deleteAfterMigration: $adminCreated,
                ),
                'description' => new L10nLanguageItemSource(
                    languageItem: $identifier . '.description',
                    deleteAfterMigration: $adminCreated,
                ),
            ],
        ];
    }
);

UserOptionCacheBuilder::getInstance()->reset();
