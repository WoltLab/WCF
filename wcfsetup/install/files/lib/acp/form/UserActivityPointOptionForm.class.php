<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectTypeAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides the user activity point option form.
 * 
 * @author	Joshua Ruesweg, Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserActivityPointOptionForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.activityPoint';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = array('admin.user.canEditActivityPoints');
	
	/**
	 * points to objectType
	 * @var	array<integer>
	 */
	public $points = [];
	
	/**
	 * valid object types
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	public $objectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['points']) && is_array($_POST['points'])) $this->points = ArrayUtil::toIntegerArray($_POST['points']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		foreach ($this->points as $objectTypeID => $points) {
			if ($points < 0) throw new UserInputException($objectTypeID, 'greaterThan');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent');
		if (empty($_POST)) {
			foreach ($this->objectTypes as $objectType) {
				$this->points[$objectType->objectTypeID] = $objectType->points;
			}
		}
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		foreach ($this->objectTypes as $objectType) {
			if (isset($this->points[$objectType->objectTypeID]) && $objectType->points != $this->points[$objectType->objectTypeID]) {
				$objectTypeAction = new ObjectTypeAction([$objectType], 'update', [
					'data' => [
						'additionalData' => serialize(array_merge($objectType->additionalData, ['points' => $this->points[$objectType->objectTypeID]]))
					]
				]);
				$objectTypeAction->executeAction(); 
                    	}
		}
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'objectTypes' => $this->objectTypes,
			'points' => $this->points
		]);
	}
}
