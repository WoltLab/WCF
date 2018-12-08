<?php
namespace wcf\data\label\group;
use wcf\data\label\LabelAction;
use wcf\data\language\item\LanguageItemAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Executes label group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label\Group
 * 
 * @method	LabelGroup		create()
 * @method	LabelGroupEditor[]	getObjects()
 * @method	LabelGroupEditor	getSingleObject()
 */
class LabelGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = LabelGroupEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		// remove labels and their potential language variables
		if (!empty($this->objectIDs)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add('groupID IN (?)', [$this->objectIDs]);
			
			$sql = "SELECT	labelID
					FROM	wcf".WCF_N."_label
					".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$labelIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			if (!empty($labelIDs)) {
				$objectAction = new LabelAction($labelIDs, 'delete');
				$objectAction->executeAction();
			}
		}
		
		$count = parent::delete();
		
		if (!empty($this->objects)) {
			// identify i18n labels
			$languageVariables = [];
			foreach ($this->objects as $labelGroup) {
				if ($labelGroup->groupName === 'wcf.acp.label.group' . $labelGroup->groupID) {
					$languageVariables[] = $labelGroup->groupName;
				}
			}
			
			// remove language variables
			if (!empty($languageVariables)) {
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add('languageItem IN (?)', [$languageVariables]);
				
				$sql = "SELECT	languageItemID
					FROM	wcf".WCF_N."_language_item
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$languageItemIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
				
				$objectAction = new LanguageItemAction($languageItemIDs, 'delete');
				$objectAction->executeAction();
			}
		}
		
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType') as $objectType) {
			$objectType->getProcessor()->save();
		}
		
		return $count;
	}
}
