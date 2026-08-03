<?php

namespace wcf\data\captcha\question;

use wcf\data\CollectionDatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\l10n\L10nDefinition;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Represents a captcha question.
 *
 * The localized values (`question` and `answers`) are stored in the
 * `wcf1_captcha_question_l10n` table.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $questionID     unique id of the captcha question
 * @property-read   0|1     $isDisabled     is `1` if the captcha question is disabled and thus not offered to answer, otherwise `0`
 * @property-read   int     $views
 * @property-read   int     $correctSubmissions
 * @property-read   int     $incorrectSubmissions
 *
 * @extends CollectionDatabaseObject<CaptchaQuestionCollection>
 */
class CaptchaQuestion extends CollectionDatabaseObject implements ITitledObject
{
    /**
     * Returns the question in the active user's language.
     */
    public function getQuestion(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'question');
    }

    /**
     * Returns the newline-separated list of answers in the active user's language.
     *
     * @since 6.3
     */
    public function getAnswers(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'answers');
    }

    /**
     * Returns true if the given user input is an answer to this question.
     */
    public function isAnswer(string $answer): bool
    {
        $answers = \explode("\n", StringUtil::unifyNewlines($this->getAnswers()));
        foreach ($answers as $__answer) {
            if (\mb_substr($__answer, 0, 1) == '~' && \mb_substr($__answer, -1, 1) == '~') {
                if (Regex::compile(\mb_substr($__answer, 1, \mb_strlen($__answer) - 2), Regex::CASE_INSENSITIVE)->match($answer)) {
                    return true;
                }

                continue;
            } elseif (\mb_strtolower($__answer) == \mb_strtolower($answer)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the localized values of this question.
     *
     * @return array<int, string>
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'question' && $columnName !== 'answers') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getQuestion();
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_captcha_question',
            'wcf1_captcha_question_l10n',
            'questionID',
            ['question', 'answers'],
        );
    }
}
