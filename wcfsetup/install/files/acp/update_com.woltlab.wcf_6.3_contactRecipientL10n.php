<?php

/**
 * Migrates the name and the email address of contact recipients from the
 * `name` and `email` columns of `wcf1_contact_recipient` (literal value or
 * language variable) into the `wcf1_contact_recipient_l10n` table.
 *
 * The administrator recipient shipped with the package is linked to its
 * language variable `wcf.contact.recipient.name1` via `l10nIdentifier`, unless
 * an administrator replaced the name with a literal value; its localized values
 * are stored as pristine copies and kept in sync with the phrase, which remains
 * in place. Its email address is always `MAIL_ADMIN_ADDRESS` and is stored as
 * an empty value. Recipients created by an administrator own their values,
 * their obsolete `wcf.contact.recipient.name<id>` and
 * `wcf.contact.recipient.email<id>` phrases are removed.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (adding the `l10nIdentifier` column and creating the
 *   `wcf1_contact_recipient_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `name` and `email` columns) must run AFTER this script.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\contact\recipient\ContactRecipient;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

$shippedLanguageItem = 'wcf.contact.recipient.name1';

// Legacy `ContactRecipient::getName()` and `getEmail()` resolved any existing
// phrase, which includes the phrases of other recipients and phrases named by
// other packages.
$sql = "SELECT  DISTINCT languageItem
        FROM    wcf1_language_item
        WHERE   languageItem IN (
                    SELECT  name
                    FROM    wcf1_contact_recipient
                    UNION
                    SELECT  email
                    FROM    wcf1_contact_recipient
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_contact_recipient_l10n")->execute();

// Link the shipped administrator recipient to its language variable. A legacy
// monolingual edit deleted the phrase and stored a literal name, such a
// recipient owns its name and stays unlinked.
$sql = "UPDATE  wcf1_contact_recipient
        SET     l10nIdentifier = CASE
                    WHEN isAdministrator = ? AND originIsSystem = ? AND name = ? THEN name
                    ELSE NULL
                END";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([1, 1, $shippedLanguageItem]);

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    ContactRecipient::getL10nDefinition(),
    static function (array $row) use ($existingLanguageItems): array {
        $nameIsLanguageItem = \in_array($row['name'], $existingLanguageItems, true);
        if ($row['isAdministrator'] === 1) {
            $emailSource = new L10nLanguageItemSource(null, '');
        } else {
            $emailIsLanguageItem = \in_array($row['email'], $existingLanguageItems, true);
            $emailSource = new L10nLanguageItemSource(
                languageItem: $emailIsLanguageItem ? $row['email'] : null,
                literal: $row['email'],
            );
        }

        return [
            'sources' => [
                'name' => new L10nLanguageItemSource(
                    languageItem: $nameIsLanguageItem ? $row['name'] : null,
                    literal: $row['name'],
                ),
                'email' => $emailSource,
            ],
        ];
    }
);

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?
                AND languageItem <> ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    '^wcf\.contact\.recipient\.(name|email)[0-9]+$',
    $shippedLanguageItem,
]);
if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
