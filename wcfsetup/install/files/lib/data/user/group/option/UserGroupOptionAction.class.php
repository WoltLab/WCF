<?php
namespace wcf\data\user\group\option;
use wcf\data\user\group\UserGroupEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes user group option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Option
 * 
 * @method	UserGroupOption			create()
 * @method	UserGroupOptionEditor[]		getObjects()
 * @method	UserGroupOptionEditor		getSingleObject()
 */
class UserGroupOptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = UserGroupOptionEditor::class;
	
	/**
	 * Updates option values for given option id.
	 */
	public function updateValues() {
		$option = current($this->objects);
		
		// remove old values
		$sql = "DELETE FROM	wcf".WCF_N."_user_group_option_value
			WHERE		optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$option->optionID
		]);
		
		if (!empty($this->parameters['values'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_group_option_value
						(optionID, groupID, optionValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->parameters['values'] as $groupID => $optionValue) {
				$statement->execute([
					$option->optionID,
					$groupID,
					$optionValue
				]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		// clear cache
		UserGroupEditor::resetCache();
	}
}
