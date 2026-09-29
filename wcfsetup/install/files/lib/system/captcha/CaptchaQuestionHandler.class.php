<?php

namespace wcf\system\captcha;

use ParagonIE\ConstantTime\Hex;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionEditor;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\StringUtil;

/**
 * Captcha handler for captcha questions.
 *
 * @author  Tim Duesterhus, Matthias Schmidt
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CaptchaQuestionHandler implements ICaptchaHandler
{
    /**
     * Separates the signed token from other values signed with `CryptoUtil`.
     */
    private const TOKEN_PREFIX = self::class . "\0";

    private const TOKEN_LIFETIME = 24 * 3600;

    /**
     * answer to the captcha question
     */
    private string $captchaAnswer = '';

    /**
     * unique identifier of the captcha question, a signed token with on-demand guest sessions
     */
    private string $captchaQuestion = '';

    /**
     * captcha question to answer
     */
    private CaptchaQuestionEditor $question;

    /**
     * list of available captcha questions
     * @var CaptchaQuestion[]
     */
    private array $questions = [];

    public function __construct()
    {
        $this->questions = CaptchaQuestionCacheBuilder::getInstance()->getData();
    }

    #[\Override]
    public function isAvailable()
    {
        return \count($this->questions) > 0;
    }

    #[\Override]
    public function getFormElement()
    {
        if (!isset($this->question)) {
            $this->readCaptchaQuestion();
        }

        $isAnswered = WCF::getSession()->getVar('captchaQuestionSolved_' . $this->captchaQuestion) !== null;

        if (!$isAnswered) {
            $this->question->updateCounters([
                'views' => 1,
            ]);
        }

        return WCF::getTPL()->render('wcf', 'shared_captchaQuestion', [
            'captchaQuestion' => $this->captchaQuestion,
            'captchaQuestionAnswered' => $isAnswered,
            'captchaQuestionObject' => $this->question,
        ]);
    }

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST['captchaQuestion'])) {
            $this->captchaQuestion = StringUtil::trim($_POST['captchaQuestion']);
        } elseif (isset($_POST['parameters']['captchaQuestion'])) {
            $this->captchaQuestion = StringUtil::trim($_POST['parameters']['captchaQuestion']);
        }
        if (isset($_POST['captchaAnswer'])) {
            $this->captchaAnswer = StringUtil::trim($_POST['captchaAnswer']);
        } elseif (isset($_POST['parameters']['captchaAnswer'])) {
            $this->captchaAnswer = StringUtil::trim($_POST['parameters']['captchaAnswer']);
        }
    }

    #[\Override]
    public function reset()
    {
        WCF::getSession()->unregister('captchaQuestion_' . $this->captchaQuestion);
        WCF::getSession()->unregister('captchaQuestionSolved_' . $this->captchaQuestion);
    }

    /**
     * Reads a random captcha question.
     */
    private function readCaptchaQuestion(): void
    {
        $questionID = \array_rand($this->questions);
        $this->question = new CaptchaQuestionEditor($this->questions[$questionID]);

        if (SessionHandler::hasOnDemandGuestSessions()) {
            // The question is carried by a signed token, because rendering a form must not
            // start a session. The random bytes make every token unique, they are recorded
            // once the question was answered to prevent the token from being reused.
            $this->captchaQuestion = CryptoUtil::createSignedString(
                self::TOKEN_PREFIX
                    . \pack('NN', $questionID, \TIME_NOW + self::TOKEN_LIFETIME)
                    . \random_bytes(16)
            );
        } else {
            // A random ID needs to be generated, otherwise an attacker will
            // trivially be able to select a specific question.
            $this->captchaQuestion = Hex::encode(\random_bytes(16));

            WCF::getSession()->register('captchaQuestion_' . $this->captchaQuestion, $questionID);
        }
    }

    /**
     * Returns the data of the signed token or `null` if the token is invalid or expired.
     *
     * @return ?array{questionID: int, expires: int, nonce: string}
     */
    private function parseToken(): ?array
    {
        $value = CryptoUtil::getValueFromSignedString($this->captchaQuestion);
        if ($value === null || !\str_starts_with($value, self::TOKEN_PREFIX)) {
            return null;
        }

        if (\strlen($value) !== \strlen(self::TOKEN_PREFIX) + 24) {
            return null;
        }

        [
            'questionID' => $questionID,
            'expires' => $expires,
            'nonce' => $nonce,
        ] = \unpack('NquestionID/Nexpires/a16nonce', $value, \strlen(self::TOKEN_PREFIX));

        if ($expires < \TIME_NOW) {
            return null;
        }

        return [
            'questionID' => $questionID,
            'expires' => $expires,
            'nonce' => $nonce,
        ];
    }

    /**
     * Records the token as used, returns false if it was used before.
     *
     * @param array{questionID: int, expires: int, nonce: string} $token
     */
    private function markTokenAsUsed(array $token): bool
    {
        $sql = "INSERT IGNORE INTO  wcf1_captcha_question_token
                                    (nonce, expires)
                VALUES              (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $token['nonce'],
            $token['expires'],
        ]);

        return $statement->getAffectedRows() === 1;
    }

    #[\Override]
    public function validate()
    {
        $token = null;
        if (SessionHandler::hasOnDemandGuestSessions()) {
            $token = $this->parseToken();
            $questionID = $token['questionID'] ?? null;
        } else {
            $questionID = WCF::getSession()->getVar('captchaQuestion_' . $this->captchaQuestion);
        }

        if ($questionID === null || !isset($this->questions[$questionID])) {
            throw new UserInputException('captchaAnswer');
        }

        $this->question = new CaptchaQuestionEditor($this->questions[$questionID]);

        // check if question has already been answered
        if (WCF::getSession()->getVar('captchaQuestionSolved_' . $this->captchaQuestion) !== null) {
            return;
        }

        if ($this->captchaAnswer === '') {
            throw new UserInputException('captchaAnswer');
        } elseif (!$this->question->isAnswer($this->captchaAnswer)) {
            $this->question->updateCounters([
                'incorrectSubmissions' => 1,
            ]);

            throw new UserInputException('captchaAnswer', 'false');
        }

        if ($token !== null && !$this->markTokenAsUsed($token)) {
            throw new UserInputException('captchaAnswer');
        }

        $this->question->updateCounters([
            'correctSubmissions' => 1,
        ]);

        WCF::getSession()->register('captchaQuestionSolved_' . $this->captchaQuestion, true);
    }

    /**
     * Deletes the records of used tokens that have expired.
     *
     * @since 6.3
     */
    public static function pruneUsedTokens(): void
    {
        $sql = "DELETE FROM wcf1_captcha_question_token
                WHERE       expires < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([\TIME_NOW]);
    }
}
