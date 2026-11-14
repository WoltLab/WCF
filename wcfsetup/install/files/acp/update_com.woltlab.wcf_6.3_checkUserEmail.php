<?php

/**
 * Aborts the upgrade if multiple users share the same email address, because
 * the update to 6.3 adds a unique key on `wcf1_user.email`.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

use wcf\system\WCF;

$sql = "SELECT   email, GROUP_CONCAT(userID ORDER BY userID SEPARATOR ', ') AS userIDs
        FROM     wcf1_user
        GROUP BY email
        HAVING   COUNT(*) > 1
        ORDER BY email";
$statement = WCF::getDB()->prepare($sql, 20);
$statement->execute();
$duplicates = $statement->fetchMap('email', 'userIDs');

if ($duplicates === []) {
    return;
}

$isGerman = WCF::getLanguage()->getFixedLanguageCode() === 'de';

$list = \implode(
    "; ",
    \array_map(
        static function (string $email, string $userIDs) use ($isGerman): string {
            if ($email === '') {
                $email = $isGerman ? '(leer)' : '(empty)';
            }

            return \sprintf('%s: %s', $email, $userIDs);
        },
        \array_keys($duplicates),
        \array_values($duplicates)
    )
);

if ($isGerman) {
    $message = \sprintf(
        'Mehrere Benutzerkonten verwenden dieselbe E-Mail-Adresse. E-Mail-Adressen müssen ab sofort eindeutig sein, bitte führen Sie die betroffenen Benutzerkonten zusammen oder ändern Sie deren E-Mail-Adressen, bevor Sie das Upgrade erneut starten. Betroffene E-Mail-Adressen (Benutzer-IDs): %s',
        $list
    );
} else {
    $message = \sprintf(
        'Multiple user accounts share the same email address. Email addresses are now required to be unique, please merge the affected user accounts or change their email addresses before restarting the upgrade. Affected email addresses (user IDs): %s',
        $list
    );
}

throw new \RuntimeException($message);
