<?php
namespace wcf\acp\form;
use wcf\data\captcha\question\CaptchaQuestionAction;
use wcf\data\captcha\question\CaptchaQuestionEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new captcha question.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class CaptchaQuestionAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.captcha.question.add';
	
	/**
	 * invalid regex in answers
	 * @var	string
	 */
	public $invalidRegex = '';
	
	/**
	 * 1 if the question is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.captcha.canManageCaptchaQuestion');
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'isDisabled' => $this->isDisabled,
			'invalidRegex' => $this->invalidRegex
		));
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('question');
		I18nHandler::getInstance()->register('answers');
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new CaptchaQuestionAction(array(), 'create', array(
			'data' => array_merge($this->additionalFields, array(
				'answers' => I18nHandler::getInstance()->isPlainValue('answers') ? I18nHandler::getInstance()->getValue('answers') : '',
				'isDisabled' => $this->isDisabled,
				'question' => I18nHandler::getInstance()->isPlainValue('question') ? I18nHandler::getInstance()->getValue('question') : ''
			))
		));
		$returnValues = $this->objectAction->executeAction();
		$questionID = $returnValues['returnValues']->questionID;
		
		// set i18n values
		$questionUpdates = array();
		if (!I18nHandler::getInstance()->isPlainValue('question')) {
			I18nHandler::getInstance()->save('question', 'wcf.captcha.question.question.question'.$questionID, 'wcf.captcha.question', 1);
			
			$questionUpdates['question'] = 'wcf.captcha.question.question.question'.$questionID;
		}
		if (!I18nHandler::getInstance()->isPlainValue('answers')) {
			I18nHandler::getInstance()->save('answers', 'wcf.captcha.question.answers.question'.$questionID, 'wcf.captcha.question', 1);
			
			$questionUpdates['answers'] = 'wcf.captcha.question.answers.question'.$questionID;
		}
		
		if (!empty($questionUpdates)) {
			$questionEditor = new CaptchaQuestionEditor($returnValues['returnValues']);
			$questionEditor->update($questionUpdates);
		}
		
		$this->saved();
		
		// reset values
		I18nHandler::getInstance()->reset();
		$this->isDisabled = 0;
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate question
		if (!I18nHandler::getInstance()->validateValue('question')) {
			if (I18nHandler::getInstance()->isPlainValue('question')) {
				throw new UserInputException('question');
			}
			else {
				throw new UserInputException('question', 'multilingual');
			}
		}
		
		// validate answers
		if (!I18nHandler::getInstance()->validateValue('answers')) {
			if (I18nHandler::getInstance()->isPlainValue('answers')) {
				throw new UserInputException('answers');
			}
			else {
				throw new UserInputException('answers', 'multilingual');
			}
		}
		
		if (I18nHandler::getInstance()->isPlainValue('answers')) {
			$answers = explode("\n", StringUtil::unifyNewlines(I18nHandler::getInstance()->getValue('answers')));
			foreach ($answers as $answer) {
				if (mb_substr($answer, 0, 1) == '~' && mb_substr($answer, -1, 1) == '~') {
					$regexLength = mb_strlen($answer) - 2;
					if (!$regexLength || !Regex::compile(mb_substr($answer, 1, $regexLength))->isValid()) {
						$this->invalidRegex = $answer;
						
						throw new UserInputException('answers', 'regexNotValid');
					}
				}
			}
		}
		foreach (I18nHandler::getInstance()->getValues('answers') as $languageAnswers) {
			$answers = explode("\n", StringUtil::unifyNewlines($languageAnswers));
			foreach ($answers as $answer) {
				if (mb_substr($answer, 0, 1) == '~' && mb_substr($answer, -1, 1) == '~') {
					$regexLength = mb_strlen($answer) - 2;
					if (!$regexLength || !Regex::compile(mb_substr($answer, 1, $regexLength))->isValid()) {
						$this->invalidRegex = $answer;
						
						throw new UserInputException('answers', 'regexNotValid');
					}
				}
			}
		}
	}
}
