<?php

namespace wcf\data\captcha\question;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Builder for creating and updating captcha questions.
 *
 * The localized values (`question` and `answers`) are stored in the
 * `wcf1_captcha_question_l10n` table, see `L10nStorage` for the expected
 * value format.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<CaptchaQuestion>
 */
final class CaptchaQuestionBuilder extends DatabaseObjectBuilder
{
    /**
     * @var array<int, string>
     */
    private array $question;

    /**
     * @var array<int, string>
     */
    private array $answers;

    /**
     * @param array<int, string> $question
     */
    public function setQuestion(array $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @param array<int, string> $answers
     */
    public function setAnswers(array $answers): static
    {
        $this->answers = $answers;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = $isDisabled ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->question) || !isset($this->answers)) {
            throw new \BadMethodCallException("Missing values for 'question' or 'answers'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $this->saveL10nValues($object);
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->question) || isset($this->answers)) {
            if (!isset($this->question) || !isset($this->answers)) {
                // `L10nStorage::setValues()` replaces all rows of the object,
                // writing only one of the two columns would wipe the other.
                throw new \BadMethodCallException("'question' and 'answers' must be set together.");
            }

            $this->saveL10nValues($object);
        }
    }

    private function saveL10nValues(CaptchaQuestion $question): void
    {
        (new L10nStorage(CaptchaQuestion::getL10nDefinition()))->setValues(
            $question->questionID,
            [
                'question' => $this->question,
                'answers' => $this->answers,
            ]
        );
    }
}
