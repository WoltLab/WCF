<?php
namespace wcf\data\user\group\option;
use wcf\data\user\group\UserGroupEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes user group option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option
 * @category	Community Framework
 */
class UserGroupOptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\group\option\UserGroupOptionEditor';
	
	/**
	 * Updates option values for given option id.
	 */
	public function updateValues() {
		$option = current($this->objects);
		
		// remove old values
		$sql = "DELETE FROM	wcf".WCF_N."_user_group_option_value
			WHERE		optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$option->optionID
		));
		
		if (!empty($this->parameters['values'])) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_group_option_value
						(optionID, groupID, optionValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->parameters['values'] as $groupID => $optionValue) {
				$statement->execute(array(
					$option->optionID,
					$groupID,
					$optionValue
				));
			}
			WCF::getDB()->commitTransaction();
		}
		
		// clear cache
		UserGroupEditor::resetCache();
	}
}
