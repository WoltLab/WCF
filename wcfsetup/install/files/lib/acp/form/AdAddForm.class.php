<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\ad\AdAction;
use wcf\form\AbstractForm;
use wcf\system\ad\AdHandler;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new ad notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class AdAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.ad.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.ad.canManageAd');
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_WCF_AD');
	
	/**
	 * html code of the ad
	 * @var	string
	 */
	public $ad = '';
	
	/**
	 * name of the notice
	 * @var	string
	 */
	public $adName = '';
	
	/**
	 * grouped ad condition object types
	 * @var	array
	 */
	public $groupedConditionObjectTypes = array();
	
	/**
	 * 1 if the ad is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * list of available location object types
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	public $locationObjectTypes = array();
	
	/**
	 * list of available locations
	 * @var	array<string>
	 */
	public $locations = array();
	
	/**
	 * id of the selected location's object type
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * order used to the show the ads
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'ad' => $this->ad,
			'adName' => $this->adName,
			'locationObjectTypes' => $this->locationObjectTypes,
			'locations' => $this->locations,
			'isDisabled' => $this->isDisabled,
			'groupedConditionObjectTypes' => $this->groupedConditionObjectTypes,
			'objectTypeID' => $this->objectTypeID,
			'showOrder' => $this->showOrder
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.ad');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditionobject) continue;
			
			if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject])) {
				$this->groupedConditionObjectTypes[$objectType->conditionobject] = array();
			}
			
			if ($objectType->conditiongroup) {
				if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup])) {
					$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup] = array();
				}
				
				$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
			}
			else {
				$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->objectTypeID] = $objectType;
			}
		}
		
		$this->locations = AdHandler::getInstance()->getLocationSelection();
		foreach (AdHandler::getInstance()->getLocationObjectTypes() as $objectType) {
			$this->locationObjectTypes[$objectType->objectTypeID] = $objectType;
		}
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['ad'])) $this->ad = StringUtil::trim($_POST['ad']);
		if (isset($_POST['adName'])) $this->adName = StringUtil::trim($_POST['adName']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['objectTypeID'])) $this->objectTypeID = intval($_POST['objectTypeID']);
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
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new AdAction(array(), 'create', array(
			'data' => array_merge($this->additionalFields, array(
				'ad' => $this->ad,
				'adName' => $this->adName,
				'isDisabled' => $this->isDisabled,
				'objectTypeID' => $this->objectTypeID,
				'showOrder' => $this->showOrder
			))
		));
		$returnValues = $this->objectAction->executeAction();
		
		// transform conditions array into one-dimensional array
		$conditions = array();
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
		
		ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->adID, $conditions);
		
		$this->saved();
		
		// reset values
		$this->ad = '';
		$this->adName = '';
		$this->isDisabled = 0;
		$this->objectTypeID = 0;
		$this->showOrder = 0;
		
		foreach ($conditions as $condition) {
			$condition->getProcessor()->reset();
		}
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->adName)) {
			throw new UserInputException('adName');
		}
		
		if (empty($this->ad)) {
			throw new UserInputException('ad');
		}
		
		if (!$this->objectTypeID) {
			throw new UserInputException('objectTypeID');
		}
		else if (!isset($this->locationObjectTypes[$this->objectTypeID])) {
			throw new UserInputException('objectTypeID', 'noValidSelection');
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
