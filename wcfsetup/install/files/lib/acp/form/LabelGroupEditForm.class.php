<?php
namespace wcf\acp\form;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupAction;
use wcf\form\AbstractForm;
use wcf\system\acl\ACLHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the label group edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LabelGroupEditForm extends LabelGroupAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.label';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.label.canManageLabel'];
	
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->groupName = 'wcf.acp.label.group'.$this->group->groupID;
		if (I18nHandler::getInstance()->isPlainValue('groupName')) {
			I18nHandler::getInstance()->remove($this->groupName);
			$this->groupName = I18nHandler::getInstance()->getValue('groupName');
		}
		else {
			I18nHandler::getInstance()->save('groupName', $this->groupName, 'wcf.acp.label', 1);
		}
		
		// update label
		$this->objectAction = new LabelGroupAction([$this->groupID], 'update', ['data' => array_merge($this->additionalFields, [
			'forceSelection' => ($this->forceSelection ? 1 : 0),
			'groupName' => $this->groupName,
			'groupDescription' => $this->groupDescription,
			'showOrder' => $this->showOrder
		])]);
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
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('groupName', 1, $this->group->groupName, 'wcf.acp.label.group\d+');
			
			$this->forceSelection = ($this->group->forceSelection ? true : false);
			$this->groupName = $this->group->groupName;
			$this->groupDescription = $this->group->groupDescription;
			$this->showOrder = $this->group->showOrder;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'groupID' => $this->groupID,
			'labelGroup' => $this->group
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function setObjectTypeRelations($data = null) {
		if (empty($_POST)) {
			// read database values
			$sql = "SELECT	objectTypeID, objectID
				FROM	wcf".WCF_N."_label_group_to_object
				WHERE	groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->groupID]);
			
			$data = [];
			while ($row = $statement->fetchArray()) {
				if (!isset($data[$row['objectTypeID']])) {
					$data[$row['objectTypeID']] = [];
				}
				
				// prevent NULL values which confuse isset()
				$data[$row['objectTypeID']][] = ($row['objectID']) ?: 0;
			}
		}
		
		parent::setObjectTypeRelations($data);
	}
}
