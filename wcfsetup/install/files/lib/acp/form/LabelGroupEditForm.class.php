<?php
namespace wcf\acp\form;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupAction;
use wcf\form\AbstractForm;
use wcf\system\acl\ACLHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the label group edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LabelGroupEditForm extends LabelGroupAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.label';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.label.canManageLabel');
	
	/**
	 * group id
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * label group object
	 * @var	\wcf\data\label\group\LabelGroup
	 */
	public $group = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->groupID = intval($_REQUEST['id']);
		$this->group = new LabelGroup($this->groupID);
		if (!$this->group->groupID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// update label
		$this->objectAction = new LabelGroupAction(array($this->groupID), 'update', array('data' => array_merge($this->additionalFields, array(
			'forceSelection' => ($this->forceSelection ? 1 : 0),
			'groupName' => $this->groupName
		))));
		$this->objectAction->executeAction();
		
		// update acl
		ACLHandler::getInstance()->save($this->groupID, $this->objectTypeID);
		ACLHandler::getInstance()->disableAssignVariables();
		
		// update object type relations
		$this->saveObjectTypeRelations($this->groupID);
		
		foreach ($this->labelObjectTypes as $objectTypeID => $labelObjectType) {
			$labelObjectType->save();
		}
		
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->forceSelection = ($this->group->forceSelection ? true : false);
			$this->groupName = $this->group->groupName;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'groupID' => $this->groupID,
			'labelGroup' => $this->group
		));
	}
	
	/**
	 * @see	\wcf\acp\form\LabelGroupAddForm::setObjectTypeRelations()
	 */
	protected function setObjectTypeRelations($data = null) {
		if (empty($_POST)) {
			// read database values
			$sql = "SELECT	objectTypeID, objectID
				FROM	wcf".WCF_N."_label_group_to_object
				WHERE	groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupID));
			
			$data = array();
			while ($row = $statement->fetchArray()) {
				if (!isset($data[$row['objectTypeID']])) {
					$data[$row['objectTypeID']] = array();
				}
				
				// prevent NULL values which confuse isset()
				$data[$row['objectTypeID']][] = ($row['objectID']) ?: 0;
			}
		}
		
		parent::setObjectTypeRelations($data);
	}
}
