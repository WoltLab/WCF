<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRankAction;
use wcf\data\user\rank\UserRankEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user rank add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserRankAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.rank.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.rank.canManageRank');
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_USER_RANK');
	
	/**
	 * rank group id
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * rank title
	 * @var	string
	 */
	public $rankTitle = '';
	
	/**
	 * CSS class name
	 * @var	string
	 */
	public $cssClassName = '';
	
	/**
	 * custom CSS class name
	 * @var	string
	 */
	public $customCssClassName = '';
	
	/**
	 * required activity points to acquire the rank
	 * @var	integer
	 */
	public $requiredPoints = 0;
	
	/**
	 * path to user rank image
	 * @var	string
	 */
	public $rankImage = '';
	
	/**
	 * number of image repeats
	 * @var	integer
	 */
	public $repeatImage = 1;
	
	/**
	 * required gender setting (1=male; 2=female)
	 * @var	integer
	 */
	public $requiredGender = 0;
	
	/**
	 * list of pre-defined css class names
	 * @var	array<string>
	 */
	public $availableCssClassNames = array(
		'yellow',
		'orange',
		'brown',
		'red',
		'pink',
		'purple',
		'blue',
		'green',
		'black',
		
		'none', /* not a real value */
		'custom' /* not a real value */
	);
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('rankTitle');
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('rankTitle')) $this->rankTitle = I18nHandler::getInstance()->getValue('rankTitle');
		if (isset($_POST['cssClassName'])) $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
		if (isset($_POST['customCssClassName'])) $this->customCssClassName = StringUtil::trim($_POST['customCssClassName']);
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['requiredPoints'])) $this->requiredPoints = intval($_POST['requiredPoints']);
		if (isset($_POST['rankImage'])) $this->rankImage = StringUtil::trim($_POST['rankImage']);
		if (isset($_POST['repeatImage'])) $this->repeatImage = intval($_POST['repeatImage']);
		if (isset($_POST['requiredGender'])) $this->requiredGender = intval($_POST['requiredGender']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate label
		if (!I18nHandler::getInstance()->validateValue('rankTitle')) {
			if (I18nHandler::getInstance()->isPlainValue('rankTitle')) {
				throw new UserInputException('rankTitle');
			}
			else {
				throw new UserInputException('rankTitle', 'multilingual');
			}
		}
		
		// validate group
		if (!$this->groupID) {
			throw new UserInputException('groupID');
		}
		$userGroup = UserGroup::getGroupByID($this->groupID);
		if ($userGroup === null || $userGroup->groupType == UserGroup::GUESTS || $userGroup->groupType == UserGroup::EVERYONE) {
			throw new UserInputException('groupID', 'notValid');
		}
		
		// css class name
		if (empty($this->cssClassName)) {
			throw new UserInputException('cssClassName', 'empty');
		}
		else if (!in_array($this->cssClassName, $this->availableCssClassNames)) {
			throw new UserInputException('cssClassName', 'notValid');
		}
		else if ($this->cssClassName == 'custom') {
			if (!empty($this->customCssClassName) && !Regex::compile('^-?[_a-zA-Z]+[_a-zA-Z0-9-]+$')->match($this->customCssClassName)) {
				throw new UserInputException('cssClassName', 'notValid');
			}
		}
		
		// required gender
		if ($this->requiredGender < 0 || $this->requiredGender > 2) {
			$this->requiredGender = 0;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save label
		$this->objectAction = new UserRankAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'rankTitle' => $this->rankTitle,
			'cssClassName' => ($this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName),
			'groupID' => $this->groupID,
			'requiredPoints' => $this->requiredPoints,
			'rankImage' => $this->rankImage,
			'repeatImage' => $this->repeatImage,
			'requiredGender' => $this->requiredGender
		))));
		$this->objectAction->executeAction();
		
		if (!I18nHandler::getInstance()->isPlainValue('rankTitle')) {
			$returnValues = $this->objectAction->getReturnValues();
			$rankID = $returnValues['returnValues']->rankID;
			I18nHandler::getInstance()->save('rankTitle', 'wcf.user.rank.userRank'.$rankID, 'wcf.user', 1);
			
			// update name
			$rankEditor = new UserRankEditor($returnValues['returnValues']);
			$rankEditor->update(array(
				'rankTitle' => 'wcf.user.rank.userRank'.$rankID
			));
		}
		$this->saved();
		
		// reset values
		$this->rankTitle = $this->cssClassName = $this->customCssClassName = $this->rankImage = '';
		$this->groupID = $this->requiredPoints = $this->requiredGender = 0;
		$this->repeatImage = 1;
		
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
			'availableCssClassNames' => $this->availableCssClassNames,
			'cssClassName' => $this->cssClassName,
			'customCssClassName' => $this->customCssClassName,
			'groupID' => $this->groupID,
			'rankTitle' => $this->rankTitle,
			'availableGroups' => UserGroup::getGroupsByType(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE)),
			'requiredPoints' => $this->requiredPoints,
			'rankImage' => $this->rankImage,
			'repeatImage' => $this->repeatImage,
			'requiredGender' => $this->requiredGender
		));
	}
}
