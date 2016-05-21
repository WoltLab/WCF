<?php
namespace wcf\acp\form;
use wcf\data\ad\Ad;
use wcf\data\ad\AdAction;
use wcf\form\AbstractForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the form to edit an ad notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class AdEditForm extends AdAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.ad';
	
	/**
	 * id of the edited ad
	 * @var	integer
	 */
	public $adID = 0;
	
	/**
	 * edited ad object
	 * @var	\wcf\data\ad\Ad
	 */
	public $adObject = null;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'adObject' => $this->adObject
		]);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->ad = $this->adObject->ad;
			$this->adName = $this->adObject->adName;
			$this->isDisabled = $this->adObject->isDisabled;
			$this->objectTypeID = $this->adObject->objectTypeID;
			$this->showOrder = $this->adObject->showOrder;
			
			$conditions = $this->adObject->getConditions();
			$conditionsByObjectTypeID = [];
			foreach ($conditions as $condition) {
				$conditionsByObjectTypeID[$condition->objectTypeID] = $condition;
			}
			
			foreach ($this->groupedConditionObjectTypes as $objectTypes1) {
				foreach ($objectTypes1 as $objectTypes2) {
					if (is_array($objectTypes2)) {
						foreach ($objectTypes2 as $objectType) {
							if (isset($conditionsByObjectTypeID[$objectType->objectTypeID])) {
								$conditionsByObjectTypeID[$objectType->objectTypeID]->getObjectType()->getProcessor()->setData($conditionsByObjectTypeID[$objectType->objectTypeID]);
							}
						}
					}
					else if (isset($conditionsByObjectTypeID[$objectTypes2->objectTypeID])) {
						$conditionsByObjectTypeID[$objectTypes2->objectTypeID]->getObjectType()->getProcessor()->setData($conditionsByObjectTypeID[$objectTypes2->objectTypeID]);
					}
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->adID = intval($_REQUEST['id']);
		$this->adObject = new Ad($this->adID);
		if (!$this->adObject->adID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new AdAction([$this->adObject], 'update', [
			'data' => array_merge($this->additionalFields, [
				'ad' => $this->ad,
				'adName' => $this->adName,
				'isDisabled' => $this->isDisabled,
				'objectTypeID' => $this->objectTypeID,
				'showOrder' => $this->showOrder
			])
		]);
		$this->objectAction->executeAction();
		
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
		
		ConditionHandler::getInstance()->updateConditions($this->adObject->adID, $this->adObject->getConditions(), $conditions);
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
}
