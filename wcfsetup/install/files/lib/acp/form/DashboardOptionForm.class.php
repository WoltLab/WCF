<?php
namespace wcf\acp\form;
use wcf\data\dashboard\box\DashboardBoxList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Provides the dashboard option form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class DashboardOptionForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.dashboard';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.dashboard.canEditDashboard');
	
	/**
	 * list of available dashboard boxes
	 * @var	array<\wcf\data\dashboard\box\DashboardBox>
	 */
	public $boxes = array();
	
	/**
	 * list of enabled box ids
	 * @var	array<integer>
	 */
	public $enabledBoxes = array();
	
	/**
	 * object type object
	 * @var	\wcf\data\object\type\ObjectType
	 */
	public $objectType = null;
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->objectTypeID = intval($_REQUEST['id']);
		
		// load object type
		$objectTypeDefinition = ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.user.dashboardContainer');
		$this->objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
		if ($this->objectType === null || $this->objectType->definitionID != $objectTypeDefinition->definitionID) {
			throw new IllegalLinkException();
		}
		
		// load available boxes
		$allowedBoxTypes = array();
		if ($this->objectType->allowcontent) $allowedBoxTypes[] = 'content';
		if ($this->objectType->allowsidebar) $allowedBoxTypes[] = 'sidebar';
		if (empty($allowedBoxTypes)) {
			// this should not happen unless you go full retard
			throw new IllegalLinkException();
		}
		
		$boxList = new DashboardBoxList();
		$boxList->getConditionBuilder()->add("dashboard_box.boxType IN (?)", array($allowedBoxTypes));
		$boxList->readObjects();
		$this->boxes = $boxList->getObjects();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['enabledBoxes']) && is_array($_POST['enabledBoxes'])) $this->enabledBoxes = ArrayUtil::toIntegerArray($_POST['enabledBoxes']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateEnabledBoxes();
	}
	
	/**
	 * Validates dashboard options.
	 */
	protected function validateEnabledBoxes() {
		foreach ($this->enabledBoxes as $boxID) {
			if (!isset($this->boxes[$boxID])) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			// load settings
			$sql = "SELECT		boxID
				FROM		wcf".WCF_N."_dashboard_option
				WHERE		objectTypeID = ?
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->objectTypeID
			));
			while ($row = $statement->fetchArray()) {
				$this->enabledBoxes[] = $row['boxID'];
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// remove previous settings
		$sql = "DELETE FROM	wcf".WCF_N."_dashboard_option
			WHERE		objectTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->objectTypeID
		));
		
		// insert new settings
		if (!empty($this->enabledBoxes)) {
			$sql = "INSERT INTO	wcf".WCF_N."_dashboard_option
						(objectTypeID, boxID, showOrder)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			$showOrder = 1;
			foreach ($this->enabledBoxes as $boxID) {
				$statement->execute(array(
					$this->objectTypeID,
					$boxID,
					$showOrder
				));
				
				$showOrder++;
			}
			WCF::getDB()->commitTransaction();
		}
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'boxes' => $this->boxes,
			'enabledBoxes' => $this->enabledBoxes,
			'objectType' => $this->objectType,
			'objectTypeID' => $this->objectTypeID
		));
	}
}
