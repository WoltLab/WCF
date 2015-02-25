<?php
namespace wcf\acp\form;
use wcf\data\bbcode\attribute\BBCodeAttributeAction;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeAction;
use wcf\data\bbcode\BBCodeEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the bbcode add form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class BBCodeAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.add';
	
	/**
	 * allowed child bbcodes
	 * @var	string
	 */
	public $allowedChildren = 'all';
	
	/**
	 * list of attributes
	 * @var	array<object>
	 */
	public $attributes = array();
	
	/**
	 * tag name
	 * @var	string
	 */
	public $bbcodeTag = '';
	
	/**
	 * editor button label
	 * @var	string
	 */
	public $buttonLabel = '';
	
	/**
	 * class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * closing html tag
	 * @var	string
	 */
	public $htmlClose = '';
	
	/**
	 * opening html tag
	 * @var	string
	 */
	public $htmlOpen = '';
	
	/**
	 * true, if bbcode contains source code
	 * @var	boolean
	 */
	public $isSourceCode = false;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.bbcode.canManageBBCode');
	
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'bbcodeAdd';
	
	/**
	 * show editor button
	 * @var	boolean
	 */
	public $showButton = false;
	
	/**
	 * wysiwyg editor icon
	 * @var	string
	 */
	public $wysiwygIcon = '';
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('buttonLabel');
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['allowedChildren'])) $this->allowedChildren = StringUtil::trim($_POST['allowedChildren']);
		if (isset($_POST['attributes'])) $this->attributes = $_POST['attributes'];
		if (isset($_POST['bbcodeTag'])) $this->bbcodeTag = mb_strtolower(StringUtil::trim($_POST['bbcodeTag']));
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['htmlClose'])) $this->htmlClose = StringUtil::trim($_POST['htmlClose']);
		if (isset($_POST['htmlOpen'])) $this->htmlOpen = StringUtil::trim($_POST['htmlOpen']);
		if (isset($_POST['isSourceCode'])) $this->isSourceCode = true;
		if (isset($_POST['showButton'])) $this->showButton = true;
		if (isset($_POST['wysiwygIcon'])) $this->wysiwygIcon = StringUtil::trim($_POST['wysiwygIcon']);
		
		// TODO: The code below violates every implicit convention of value reading and type casting
		$attributeNo = 0;
		foreach ($this->attributes as $key => $val) {
			$val['attributeNo'] = $attributeNo++;
			$val['required'] = (int) isset($val['required']);
			$val['useText'] = (int) isset($val['useText']);
			$this->attributes[$key] = (object) $val;
		}
		
		I18nHandler::getInstance()->readValues();
		$this->readButtonLabelFormParameter();
	}
	
	/**
	 * Reads the form parameter for the button label.
	 */
	protected function readButtonLabelFormParameter() {
		if (I18nHandler::getInstance()->isPlainValue('buttonLabel')) $this->buttonLabel = I18nHandler::getInstance()->getValue('buttonLabel');
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// tag name must not be empty
		if (empty($this->bbcodeTag)) {
			throw new UserInputException('bbcodeTag');
		}
		
		// tag may only contain alphanumeric chars
		if (!Regex::compile('^[a-z0-9]+$', Regex::CASE_INSENSITIVE)->match($this->bbcodeTag)) {
			throw new UserInputException('bbcodeTag', 'notValid');
		}
		
		// disallow the Pseudo-BBCodes all and none
		if ($this->bbcodeTag == 'all' || $this->bbcodeTag == 'none') {
			throw new UserInputException('bbcodeTag', 'notValid');
		}
		
		// check whether the tag is in use
		$bbcode = BBCode::getBBCodeByTag($this->bbcodeTag);
		if ((!isset($this->bbcode) && $bbcode->bbcodeID) || (isset($this->bbcode) && $bbcode->bbcodeID != $this->bbcode->bbcodeID)) {
			throw new UserInputException('bbcodeTag', 'inUse');
		}
		
		// handle empty case first
		if (empty($this->allowedChildren)) {
			throw new UserInputException('allowedChildren');
		}
		
		// validate syntax of allowedChildren: Optional all|none^ followed by a comma-separated list of bbcodes
		if (!empty($this->allowedChildren) && !Regex::compile('^(?:(?:all|none)\^)?(?:[a-zA-Z0-9]+,)*[a-zA-Z0-9]+$')->match($this->allowedChildren)) {
			throw new UserInputException('allowedChildren', 'notValid');
		}
		
		// validate class
		if (!empty($this->className) && !class_exists($this->className)) {
			throw new UserInputException('className', 'notFound');
		}
		
		// validate attributes
		foreach ($this->attributes as $attribute) {
			// Check whether the pattern is a valid regex
			if (!Regex::compile($attribute->validationPattern)->isValid()) {
				throw new UserInputException('attributeValidationPattern'.$attribute->attributeNo, 'notValid');
			}
		}
		
		// button
		if ($this->showButton) {
			// validate label
			if (!I18nHandler::getInstance()->validateValue('buttonLabel')) {
				if (I18nHandler::getInstance()->isPlainValue('buttonLabel')) {
					throw new UserInputException('buttonLabel');
				}
				else {
					throw new UserInputException('buttonLabel', 'multilingual');
				}
			}
			
			// validate image path
			if (empty($this->wysiwygIcon)) {
				throw new UserInputException('wysiwygIcon');
			}
		}
		else {
			$this->buttonLabel = '';
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save bbcode
		$this->objectAction = new BBCodeAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'allowedChildren' => $this->allowedChildren,
			'bbcodeTag' => $this->bbcodeTag,
			'buttonLabel' => $this->buttonLabel,
			'className' => $this->className,
			'htmlOpen' => $this->htmlOpen,
			'htmlClose' => $this->htmlClose,
			'isSourceCode' => ($this->isSourceCode ? 1 : 0),
			'packageID' => 1,
			'showButton' => ($this->showButton ? 1 : 0),
			'wysiwygIcon' => $this->wysiwygIcon
		))));
		$returnValues = $this->objectAction->executeAction();
		foreach ($this->attributes as $attribute) {
			$attributeAction = new BBCodeAttributeAction(array(), 'create', array('data' => array(
				'bbcodeID' => $returnValues['returnValues']->bbcodeID,
				'attributeNo' => $attribute->attributeNo,
				'attributeHtml' => $attribute->attributeHtml,
				'validationPattern' => $attribute->validationPattern,
				'required' => $attribute->required,
				'useText' => $attribute->useText,
			)));
			$attributeAction->executeAction();
		}
		
		if ($this->showButton && !I18nHandler::getInstance()->isPlainValue('buttonLabel')) {
			$bbcodeID = $returnValues['returnValues']->bbcodeID;
			I18nHandler::getInstance()->save('buttonLabel', 'wcf.bbcode.buttonLabel'.$bbcodeID, 'wcf.bbcode', 1);
			
			// update button label
			$bbcodeEditor = new BBCodeEditor($returnValues['returnValues']);
			$bbcodeEditor->update(array(
				'buttonLabel' => 'wcf.bbcode.buttonLabel'.$bbcodeID
			));
		}
		
		$this->saved();
		
		// reset values
		$this->bbcodeTag = $this->htmlOpen = $this->htmlClose = $this->className = $this->buttonLabel = $this->wysiwygIcon = '';
		$this->allowedChildren = 'all';
		$this->attributes = array();
		$this->isSourceCode = $this->showButton = false;
		
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'allowedChildren' => $this->allowedChildren,
			'attributes' => $this->attributes,
			'bbcodeTag' => $this->bbcodeTag,
			'buttonLabel' => $this->buttonLabel,
			'className' => $this->className,
			'htmlOpen' => $this->htmlOpen,
			'htmlClose' => $this->htmlClose,
			'isSourceCode' => $this->isSourceCode,
			'showButton' => $this->showButton,
			'wysiwygIcon' => $this->wysiwygIcon
		));
	}
}
