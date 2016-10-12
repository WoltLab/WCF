<?php
namespace wcf\system\captcha;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Captcha handler for captcha questions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Captcha
 */
class CaptchaQuestionHandler implements ICaptchaHandler {
	/**
	 * answer to the captcha question
	 * @var	string
	 */
	protected $captchaAnswer = '';
	
	/**
	 * unique identifier of the captcha question
	 * @var	string
	 */
	protected $captchaQuestion = '';
	
	/**
	 * captcha question to answer
	 * @var	CaptchaQuestion
	 */
	protected $question = null;
	
	/**
	 * list of available captcha questions
	 * @var	CaptchaQuestion[]
	 */
	protected $questions = [];
	
	/**
	 * Creates a new instance of CaptchaQuestionHandler.
	 */
	public function __construct() {
		$this->questions = CaptchaQuestionCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailable() {
		return count($this->questions) > 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement() {
		if ($this->question === null) {
			$this->readCaptchaQuestion();
		}
		
		return WCF::getTPL()->fetch('captchaQuestion', 'wcf', [
			'captchaQuestion' => $this->captchaQuestion,
			'captchaQuestionAnswered' => WCF::getSession()->getVar('captchaQuestionSolved_'.$this->captchaQuestion) !== null,
			'captchaQuestionObject' => $this->question
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['captchaQuestion'])) $this->captchaQuestion = StringUtil::trim($_POST['captchaQuestion']);
		if (isset($_POST['captchaAnswer'])) $this->captchaAnswer = StringUtil::trim($_POST['captchaAnswer']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		WCF::getSession()->unregister('captchaQuestion_'.$this->captchaQuestion);
		WCF::getSession()->unregister('captchaQuestionSolved_'.$this->captchaQuestion);
	}
	
	/**
	 * Reads a random captcha question.
	 */
	protected function readCaptchaQuestion() {
		$questionID = array_rand($this->questions);
		$this->question = $this->questions[$questionID];
		
		do {
			$this->captchaQuestion = StringUtil::getRandomID();
		}
		while (WCF::getSession()->getVar('captchaQuestion_'.$this->captchaQuestion) !== null);
		
		WCF::getSession()->register('captchaQuestion_'.$this->captchaQuestion, $questionID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$questionID = WCF::getSession()->getVar('captchaQuestion_'.$this->captchaQuestion);
		
		if ($questionID === null || !isset($this->questions[$questionID])) {
			throw new UserInputException('captchaQuestion');
		}
		
		$this->question = $this->questions[$questionID];
		
		// check if question has already been answered
		if (WCF::getSession()->getVar('captchaQuestionSolved_'.$this->captchaQuestion) !== null) return;
		
		if ($this->captchaAnswer == '') {
			throw new UserInputException('captchaAnswer');
		}
		else if (!$this->question->isAnswer($this->captchaAnswer)) {
			throw new UserInputException('captchaAnswer', 'false');
		}
		
		WCF::getSession()->register('captchaQuestionSolved_'.$this->captchaQuestion, true);
	}
}
