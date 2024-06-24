<?php

namespace wcf\system\captcha;

use ParagonIE\ConstantTime\Hex;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionEditor;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
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
     * answer to the captcha question
     * @var string
     */
    protected $captchaAnswer = '';

    /**
     * unique identifier of the captcha question
     * @var string
     */
    protected $captchaQuestion = '';

    /**
     * captcha question to answer
     */
    protected CaptchaQuestionEditor $question;

    /**
     * list of available captcha questions
     * @var CaptchaQuestion[]
     */
    protected $questions = [];

    /**
     * Creates a new instance of CaptchaQuestionHandler.
     */
    public function __construct()
    {
        $this->questions = CaptchaQuestionCacheBuilder::getInstance()->getData();
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        return \count($this->questions) > 0;
    }

    /**
     * @inheritDoc
     */
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

        return WCF::getTPL()->fetch('shared_captchaQuestion', 'wcf', [
            'captchaQuestion' => $this->captchaQuestion,
            'captchaQuestionAnswered' => $isAnswered,
            'captchaQuestionObject' => $this->question,
        ]);
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function reset()
    {
        WCF::getSession()->unregister('captchaQuestion_' . $this->captchaQuestion);
        WCF::getSession()->unregister('captchaQuestionSolved_' . $this->captchaQuestion);
    }

    /**
     * Reads a random captcha question.
     */
    protected function readCaptchaQuestion()
    {
        $questionID = \array_rand($this->questions);
        $this->question = new CaptchaQuestionEditor($this->questions[$questionID]);

        // A random ID needs to be generated, otherwise an attacker will
        // trivially be able to select a specific question.
        $this->captchaQuestion = Hex::encode(\random_bytes(16));

        WCF::getSession()->register('captchaQuestion_' . $this->captchaQuestion, $questionID);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $questionID = WCF::getSession()->getVar('captchaQuestion_' . $this->captchaQuestion);

        if ($questionID === null || !isset($this->questions[$questionID])) {
            throw new UserInputException('captchaAnswer');
        }

        $this->question = new CaptchaQuestionEditor($this->questions[$questionID]);

        // check if question has already been answered
        if (WCF::getSession()->getVar('captchaQuestionSolved_' . $this->captchaQuestion) !== null) {
            return;
        }

        if ($this->captchaAnswer == '') {
            throw new UserInputException('captchaAnswer');
        } elseif (!$this->question->isAnswer($this->captchaAnswer)) {
            $this->question->updateCounters([
                'incorrectSubmissions' => 1,
            ]);

            throw new UserInputException('captchaAnswer', 'false');
        }

        $this->question->updateCounters([
            'correctSubmissions' => 1,
        ]);

        WCF::getSession()->register('captchaQuestionSolved_' . $this->captchaQuestion, true);
    }
}
