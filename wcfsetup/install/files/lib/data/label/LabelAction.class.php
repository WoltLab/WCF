<?php
namespace wcf\data\label;
use wcf\data\language\item\LanguageItemAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Executes label-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label
 * @category	Community Framework
 */
class LabelAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\label\LabelEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.content.label.canManageLabel');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.label.canManageLabel');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.label.canManageLabel');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		parent::delete();
		
		if (!empty($this->objects)) {
			// identify i18n labels
			$languageVariables = array();
			foreach ($this->objects as $object) {
				if (preg_match('~wcf.acp.label.label\d+~', $object->label)) {
					$languageVariables[] = $object->label;
				}
			}
			
			// remove language variables
			if (!empty($languageVariables)) {
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("languageItem IN (?)", array($languageVariables));
				
				$sql = "SELECT	languageItemID
					FROM	wcf".WCF_N."_language_item
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$languageItemIDs = array();
				while ($row = $statement->fetchArray()) {
					$languageItemIDs[] = $row['languageItemID'];
				}
				
				$objectAction = new LanguageItemAction($languageItemIDs, 'delete');
				$objectAction->executeAction();
			}
		}
	}
}
