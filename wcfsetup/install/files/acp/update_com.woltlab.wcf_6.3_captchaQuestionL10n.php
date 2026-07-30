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

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\l10n\L10nLanguageItemSource;
use wcf\system\l10n\L10nLanguageItemSync;
use wcf\system\WCF;

// This script owns the table's content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_captcha_question_l10n")->execute();

L10nLanguageItemSync::migrate(
    CaptchaQuestion::getL10nDefinition(),
    static function (array $row): array {
        $questionIsPhrase = (bool)\preg_match(
            '~^wcf\.captcha\.question\.question\.question\d+$~',
            $row['question']
        );
        $answersIsPhrase = $row['answers'] !== null && (bool)\preg_match(
            '~^wcf\.captcha\.question\.answers\.question\d+$~',
            $row['answers']
        );

        return [
            'sources' => [
                'question' => new L10nLanguageItemSource(
                    languageItem: $questionIsPhrase ? $row['question'] : null,
                    literal: $row['question'],
                    deleteAfterMigration: true,
                ),
                'answers' => new L10nLanguageItemSource(
                    languageItem: $answersIsPhrase ? $row['answers'] : null,
                    literal: $row['answers'],
                    deleteAfterMigration: true,
                ),
            ],
        ];
    }
);

// Cached question objects were created without their localized values.
CaptchaQuestionCacheBuilder::getInstance()->reset();
