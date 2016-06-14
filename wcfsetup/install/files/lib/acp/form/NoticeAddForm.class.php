<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\notice\NoticeAction;
use wcf\data\notice\NoticeEditor;
use wcf\form\AbstractForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class NoticeAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.notice.add';
	
	/**
	 * list pf pre-defined CSS class names
	 * @var	string[]
	 */
	public $availableCssClassNames = [
		'info',
		'success',
		'warning',
		'error',
		
		'custom'
	];
	
	/**
	 * name of the chosen CSS class name
	 * @var	string
	 */
	public $cssClassName = 'info';
	
	/**
	 * custom CSS class name
	 * @var	string
	 */
	public $customCssClassName = '';
	
	/**
	 * grouped notice condition object types
	 * @var	ObjectType[][]
	 */
	public $groupedConditionObjectTypes = [];
	
	/**
	 * 1 if the notice is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * 1 if the notice is dismissible
	 * @var	integer
	 */
	public $isDismissible = 0;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.notice.canManageNotice'];
	
	/**
	 * name of the notice
	 * @var	string
	 */
	public $noticeName = '';
	
	/**
	 * 1 if html is used in the notice text
	 * @var	integer
	 */
	public $noticeUseHtml = 0;
	
	/**
	 * order used to the show the notices
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'availableCssClassNames' => $this->availableCssClassNames,
			'cssClassName' => $this->cssClassName,
			'customCssClassName' => $this->customCssClassName,
			'isDisabled' => $this->isDisabled,
			'isDismissible' => $this->isDismissible,
			'groupedConditionObjectTypes' => $this->groupedConditionObjectTypes,
			'noticeName' => $this->noticeName,
			'noticeUseHtml' => $this->noticeUseHtml,
			'showOrder' => $this->showOrder
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.notice');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditionobject) continue;
			
			if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject])) {
				$this->groupedConditionObjectTypes[$objectType->conditionobject] = [];
			}
			
			if ($objectType->conditiongroup) {
				if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup])) {
					$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup] = [];
				}
				
				$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
			}
			else {
				$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->objectTypeID] = $objectType;
			}
		}
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['cssClassName'])) $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
		if (isset($_POST['customCssClassName'])) $this->customCssClassName = StringUtil::trim($_POST['customCssClassName']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['isDismissible'])) $this->isDismissible = 1;
		if (isset($_POST['noticeName'])) $this->noticeName = StringUtil::trim($_POST['noticeName']);
		if (isset($_POST['noticeUseHtml'])) $this->noticeUseHtml = 1;
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		
		foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectTypes) {
				if (is_array($objectTypes)) {
					foreach ($objectTypes as $objectType) {
						$objectType->getProcessor()->readFormParameters();
					}
				}
				else {
					$objectTypes->getProcessor()->readFormParameters();
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('notice');
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new NoticeAction([], 'create', [
			'data' => array_merge($this->additionalFields, [
				'cssClassName' => $this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName,
				'isDisabled' => $this->isDisabled,
				'isDismissible' => $this->isDismissible,
				'notice' => I18nHandler::getInstance()->isPlainValue('notice') ? I18nHandler::getInstance()->getValue('notice') : '',
				'noticeName' => $this->noticeName,
				'noticeUseHtml' => $this->noticeUseHtml,
				'showOrder' => $this->showOrder
			])
		]);
		$returnValues = $this->objectAction->executeAction();
		
		if (!I18nHandler::getInstance()->isPlainValue('notice')) {
			I18nHandler::getInstance()->save('notice', 'wcf.notice.notice.notice'.$returnValues['returnValues']->noticeID, 'wcf.notice', 1);
			
			// update notice name
			$noticeEditor = new NoticeEditor($returnValues['returnValues']);
			$noticeEditor->update([
				'notice' => 'wcf.notice.notice.notice'.$returnValues['returnValues']->noticeID
			]);
		}
		
		// transform conditions array into one-dimensional array
		$conditions = [];
		foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectTypes) {
				if (is_array($objectTypes)) {
					$conditions = array_merge($conditions, $objectTypes);
				}
				else {
					$conditions[] = $objectTypes;
				}
			}
		}
		
		ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->noticeID, $conditions);
		
		$this->saved();
		
		// reset values
		$this->cssClassName = '';
		$this->customCssClassName = '';
		$this->isDisabled = 0;
		$this->isDismissible = 0;
		$this->noticeName = '';
		$this->noticeUseHtml = 0;
		$this->showOrder = 0;
		I18nHandler::getInstance()->reset();
		
		foreach ($conditions as $condition) {
			$condition->getProcessor()->reset();
		}
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->noticeName)) {
			throw new UserInputException('noticeName');
		}
		
		if (!I18nHandler::getInstance()->validateValue('notice')) {
			if (I18nHandler::getInstance()->isPlainValue('notice')) {
				throw new UserInputException('notice');
			}
			else {
				throw new UserInputException('notice', 'multilingual');
			}
		}
		
		// validate class name
		if (empty($this->cssClassName)) {
			throw new UserInputException('cssClassName');
		}
		else if (!in_array($this->cssClassName, $this->availableCssClassNames)) {
			throw new UserInputException('cssClassName', 'notValid');
		}
		else if ($this->cssClassName == 'custom') {
			if (empty($this->cssClassName)) {
				throw new UserInputException('cssClassName');
			}
			if (!Regex::compile('^-?[_a-zA-Z]+[_a-zA-Z0-9-]+$')->match($this->customCssClassName)) {
				throw new UserInputException('cssClassName', 'notValid');
			}
		}
		
		foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectTypes) {
				if (is_array($objectTypes)) {
					foreach ($objectTypes as $objectType) {
						$objectType->getProcessor()->validate();
					}
				}
				else {
					$objectTypes->getProcessor()->validate();
				}
			}
		}
	}
}
