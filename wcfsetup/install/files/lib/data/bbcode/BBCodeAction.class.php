<?php
namespace wcf\data\bbcode;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Executes bbcode-related actions.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode
 * @category	Community Framework
 */
class BBCodeAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\bbcode\BBCodeEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.bbcode.canManageBBCode');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.bbcode.canManageBBCode');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('delete', 'toggle', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$bbCode = parent::create();
		
		// add bbcode to BBCodeSelect user group options
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_group_option
			WHERE	optionType = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('BBCodeSelect'));
		$optionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($optionIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add("optionID IN (?)", array($optionIDs));
			$conditionBuilder->add("groupID IN (?)", array(UserGroup::getGroupIDsByType(array(UserGroup::EVERYONE))));
			$conditionBuilder->add("optionValue <> ?", array('all'));
			
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_group_option_value
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			
			$sql = "UPDATE	wcf".WCF_N."_user_group_option_value
				SET	optionValue = ?
				WHERE	optionID = ?
					AND groupID = ?";
			$updateStatement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			while ($row = $statement->fetchArray()) {
				if (!empty($row['optionValue'])) {
					$row['optionValue'] .= ','.$bbCode->bbcodeTag;
				}
				else {
					$row['optionValue'] = $bbCode->bbcodeTag;
				}
				
				$updateStatement->execute(array(
					$row['optionValue'],
					$row['optionID'],
					$row['groupID']
				));
			}
			WCF::getDB()->commitTransaction();
			
			// clear user group option cache
			UserGroupEditor::resetCache();
		}
		
		return $bbCode;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateDelete()
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $bbcode) {
			if (!$bbcode->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $bbcode) {
			$bbcode->update(array(
				'isDisabled' => $bbcode->isDisabled ? 0 : 1
			));
		}
	}
}
