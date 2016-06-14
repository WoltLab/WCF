<?php
namespace wcf\acp\form;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the form to edit an existing captcha question.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class CaptchaQuestionEditForm extends CaptchaQuestionAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.captcha';
	
	/**
	 * edited captcha question
	 * @var	\wcf\data\captcha\question\CaptchaQuestion
	 */
	public $captchaQuestion = null;
	
	/**
	 * id of the edited captcha question
	 * @var	integer
	 */
	public $captchaQuestionID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'captchaQuestion' => $this->captchaQuestion
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('question', 1, $this->captchaQuestion->question, 'wcf.captcha.question.question.question\d+');
			I18nHandler::getInstance()->setOptions('answers', 1, $this->captchaQuestion->answers, 'wcf.captcha.question.question.answers\d+');
			
			$this->isDisabled = $this->captchaQuestion->isDisabled;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->captchaQuestionID = intval($_REQUEST['id']);
		$this->captchaQuestion = new CaptchaQuestion($this->captchaQuestionID);
		if (!$this->captchaQuestion->questionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		if (I18nHandler::getInstance()->isPlainValue('question')) {
			if ($this->captchaQuestion->question == 'wcf.captcha.question.question.question'.$this->captchaQuestion->questionID) {
				I18nHandler::getInstance()->remove($this->captchaQuestion->question);
			}
		}
		else {
			I18nHandler::getInstance()->save('question', 'wcf.captcha.question.question.question'.$this->captchaQuestion->questionID, 'wcf.captcha.question', 1);
		}
		
		if (I18nHandler::getInstance()->isPlainValue('answers')) {
			if ($this->captchaQuestion->answers == 'wcf.captcha.question.question.answers'.$this->captchaQuestion->questionID) {
				I18nHandler::getInstance()->remove($this->captchaQuestion->answers);
			}
		}
		else {
			I18nHandler::getInstance()->save('answers', 'wcf.captcha.question.question.answers'.$this->captchaQuestion->questionID, 'wcf.captcha.question', 1);
		}
		
		$this->objectAction = new CaptchaQuestionAction([$this->captchaQuestion], 'update', [
			'data' => array_merge($this->additionalFields, [
				'answers' => I18nHandler::getInstance()->isPlainValue('answers') ? I18nHandler::getInstance()->getValue('answers') : 'wcf.captcha.question.question.answers'.$this->captchaQuestion->questionID,
				'isDisabled' => $this->isDisabled,
				'question' => I18nHandler::getInstance()->isPlainValue('question') ? I18nHandler::getInstance()->getValue('question') : 'wcf.captcha.question.question.question'.$this->captchaQuestion->questionID
			])
		]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
