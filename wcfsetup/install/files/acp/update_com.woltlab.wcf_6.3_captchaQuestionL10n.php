<?php

/**
 * Migrates the captcha question values (`question` and `answers`) from the
 * columns of `wcf1_captcha_question` (literal values or i18n phrases) into the
 * `wcf1_captcha_question_l10n` table.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `wcf1_captcha_question_l10n` table) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_captchaQuestion.php`
 *   (dropping the migrated columns) must run AFTER this script.
 */

use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

// This script owns the table's content at this point (idempotency on re-runs).
$sql = "DELETE FROM wcf1_captcha_question_l10n";
WCF::getDB()->prepare($sql)->execute();

$sql = "SELECT  questionID, question, answers
        FROM    wcf1_captcha_question";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
$rows = [];
while ($row = $statement->fetchArray()) {
    $rows[] = $row;
}

$installedLanguageIDs = \array_keys(LanguageFactory::getInstance()->getLanguages());
$defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();

$fetchItemsStatement = WCF::getDB()->prepare(
    "SELECT languageID, languageItemValue
     FROM   wcf1_language_item
     WHERE  languageItem = ?"
);
$fetchItems = static function (string $languageItem) use ($fetchItemsStatement): array {
    $fetchItemsStatement->execute([$languageItem]);

    return $fetchItemsStatement->fetchMap('languageID', 'languageItemValue');
};

// Mirrors the phrase fallback semantics: value of the requested language,
// value of the default language, any value, literal column value.
$resolve = static function (?array $items, ?string $literal, int $languageID) use ($defaultLanguageID): ?string {
    if ($items === null) {
        return $literal;
    }
    if (\array_key_exists($languageID, $items)) {
        return $items[$languageID];
    }
    if (\array_key_exists($defaultLanguageID, $items)) {
        return $items[$defaultLanguageID];
    }

    return $items !== [] ? \reset($items) : $literal;
};

$obsoleteItems = [];
$insertStatement = WCF::getDB()->prepare(
    "INSERT INTO wcf1_captcha_question_l10n (questionID, languageID, question, answers)
     VALUES      (?, ?, ?, ?)"
);

WCF::getDB()->beginTransaction();
foreach ($rows as $row) {
    $questionItems = null;
    if (\preg_match('~^wcf\.captcha\.question\.question\.question\d+$~', $row['question'])) {
        $items = $fetchItems($row['question']);
        if ($items !== []) {
            $questionItems = $items;
            $obsoleteItems[] = $row['question'];
        }
        // Phrase name stored but items are missing: Treat the value as
        // literal text, mirroring the recovery in `TI18nFormField`.
    }

    $answersItems = null;
    if (
        $row['answers'] !== null
        && \preg_match('~^wcf\.captcha\.question\.answers\.question\d+$~', $row['answers'])
    ) {
        $items = $fetchItems($row['answers']);
        if ($items !== []) {
            $answersItems = $items;
            $obsoleteItems[] = $row['answers'];
        }
    }

    // Consistency rule of the l10n storage: an object is either monolingual
    // (a single row with `languageID IS NULL`) or multilingual (one row per
    // language). The language set is the union of the phrase languages, a
    // literal or missing side is filled per language via the fallback chain.
    $languageIDs = \array_values(\array_intersect(
        \array_unique([
            ...\array_keys($questionItems ?? []),
            ...\array_keys($answersItems ?? []),
        ]),
        $installedLanguageIDs
    ));

    if ($languageIDs === []) {
        $insertStatement->execute([
            $row['questionID'],
            null,
            $row['question'],
            $row['answers'],
        ]);

        continue;
    }

    foreach ($languageIDs as $languageID) {
        $insertStatement->execute([
            $row['questionID'],
            $languageID,
            $resolve($questionItems, $row['question'], $languageID) ?? '',
            $resolve($answersItems, $row['answers'], $languageID),
        ]);
    }
}
WCF::getDB()->commitTransaction();

// Remove the migrated phrases.
if ($obsoleteItems !== []) {
    foreach (\array_chunk(\array_unique($obsoleteItems), 100) as $chunk) {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('languageItem IN (?)', [$chunk]);

        $sql = "DELETE FROM wcf1_language_item
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
    }

    LanguageFactory::getInstance()->deleteLanguageCache();
}

// Cached question objects were created without their localized values.
CaptchaQuestionCacheBuilder::getInstance()->reset();
