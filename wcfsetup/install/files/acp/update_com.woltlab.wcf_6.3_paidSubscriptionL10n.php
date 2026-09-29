<?php

/**
 * Migrates the title and the description of paid subscriptions from the
 * `title` and `description` columns of `wcf1_paid_subscription` (literal value
 * or language variable) into the `wcf1_paid_subscription_l10n` table.
 *
 * Descriptions stored as HTML were saved without an input processor, they are
 * processed like any description saved from 6.3 on. The obsolete
 * `wcf.paidSubscription.subscription<id>` and
 * `wcf.paidSubscription.subscription<id>.description` phrases are removed,
 * including those left behind by previously deleted subscriptions.
 *
 * The positions of the subscriptions are renumbered consecutively, because
 * the show order is now maintained relative to the other subscriptions.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_paid_subscription_l10n` table) must run BEFORE this
 *   script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `title` and `description` columns) must run AFTER this
 *   script.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\paid\subscription\PaidSubscription;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

$languageItemPattern = '^wcf\.paidSubscription\.subscription[0-9]+(\.description)?$';

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_paid_subscription_l10n")->execute();

// The length of phrases was never validated, and the language editor allows
// phrases of any length, which would exceed the l10n columns. The phrases are
// removed below anyway, shortening them is lossless for a retry. A shortened
// HTML description is repaired by the input processor below.
$sql = "UPDATE  wcf1_language_item
        SET     languageItemValue = SUBSTRING(languageItemValue, 1, 255)
        WHERE   languageItem REGEXP ?
            AND CHAR_LENGTH(languageItemValue) > 255";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.paidSubscription\.subscription[0-9]+$']);

$sql = "UPDATE  wcf1_language_item
        SET     languageItemValue = SUBSTRING(languageItemValue, 1, 16383)
        WHERE   languageItem REGEXP ?
            AND LENGTH(languageItemValue) > 65535";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['^wcf\.paidSubscription\.subscription[0-9]+\.description$']);

// Legacy `PaidSubscription::getTitle()` resolved any existing phrase, which
// includes the phrases of other subscriptions and phrases named by other
// packages. `getDescription()` only resolved the subscription phrase pattern.
// Phrases that are not removed below keep their value, a value that exceeds
// the l10n column would block the update, the phrase name is migrated instead.
$sql = "SELECT      languageItem
        FROM        wcf1_language_item
        WHERE       languageItem IN (
                        SELECT  title
                        FROM    wcf1_paid_subscription
                    )
        GROUP BY    languageItem
        HAVING      MAX(CHAR_LENGTH(languageItemValue)) <= 255";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingTitleLanguageItems = $statement->fetchAll(\PDO::FETCH_COLUMN);

$sql = "SELECT      languageItem
        FROM        wcf1_language_item
        WHERE       languageItem IN (
                        SELECT  description
                        FROM    wcf1_paid_subscription
                    )
        GROUP BY    languageItem
        HAVING      MAX(LENGTH(languageItemValue)) <= 65535";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$existingDescriptionLanguageItems = \array_filter(
    $statement->fetchAll(\PDO::FETCH_COLUMN),
    static fn(string $languageItem) => \preg_match('~^wcf.paidSubscription.subscription\d+.description$~', $languageItem) === 1
);

$sql = "SELECT      subscriptionID
        FROM        wcf1_paid_subscription
        ORDER BY    showOrder, subscriptionID";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$subscriptionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

$sql = "UPDATE  wcf1_paid_subscription
        SET     showOrder = ?
        WHERE   subscriptionID = ?";
$statement = WCF::getDB()->prepare($sql);
foreach ($subscriptionIDs as $index => $subscriptionID) {
    $statement->execute([
        $index + 1,
        $subscriptionID,
    ]);
}

// The phrases are removed only by the final `DELETE` below, so that a failure
// before it leaves them in place for a retry.
L10nLanguageItemSync::migrate(
    PaidSubscription::getL10nDefinition(),
    static function (array $row) use ($existingTitleLanguageItems, $existingDescriptionLanguageItems): array {
        $description = $row['description'] ?? '';

        return [
            'sources' => [
                'title' => new L10nLanguageItemSource(
                    languageItem: \in_array($row['title'], $existingTitleLanguageItems, true) ? $row['title'] : null,
                    literal: $row['title'],
                ),
                'description' => new L10nLanguageItemSource(
                    languageItem: \in_array($description, $existingDescriptionLanguageItems, true) ? $description : null,
                    literal: $description,
                ),
            ],
        ];
    }
);

$sql = "SELECT  subscriptionID, languageID, description
        FROM    wcf1_paid_subscription_l10n";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

$sql = "UPDATE  wcf1_paid_subscription_l10n
        SET     description = ?
        WHERE   subscriptionID = ?
            AND languageID <=> ?";
$updateStatement = WCF::getDB()->prepare($sql);
foreach ($rows as $row) {
    // Descriptions without HTML are legacy plain text, which is still rendered
    // as such by `PaidSubscription::getFormattedDescription()`.
    if ($row['description'] === null || \preg_match('~^<[a-z]+~', $row['description']) !== 1) {
        continue;
    }

    // The processed HTML can be longer than its source and exceed the column.
    $description = $row['description'];
    do {
        $htmlInputProcessor = new HtmlInputProcessor();
        $htmlInputProcessor->process($description, 'com.woltlab.wcf.paidSubscription', $row['subscriptionID']);
        $html = $htmlInputProcessor->getHtml();

        $description = \mb_substr($description, 0, (int)(\mb_strlen($description) * 0.9));
    } while (\strlen($html) > 65535);

    $updateStatement->execute([
        $html,
        $row['subscriptionID'],
        $row['languageID'],
    ]);
}

$sql = "DELETE FROM wcf1_language_item
        WHERE       languageItem REGEXP ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([$languageItemPattern]);
if ($statement->getAffectedRows() > 0) {
    LanguageFactory::getInstance()->deleteLanguageCache();
}
