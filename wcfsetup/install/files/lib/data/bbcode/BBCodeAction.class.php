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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode
 * @category	Community Framework
 */
class BBCodeAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = BBCodeEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.bbcode.canManageBBCode'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.bbcode.canManageBBCode'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['delete', 'toggle', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		$bbCode = parent::create();
		
		// add bbcode to BBCodeSelect user group options
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_group_option
			WHERE	optionType = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['BBCodeSelect']);
		$optionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($optionIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add("optionID IN (?)", [$optionIDs]);
			$conditionBuilder->add("groupID IN (?)", [UserGroup::getGroupIDsByType([UserGroup::EVERYONE])]);
			$conditionBuilder->add("optionValue <> ?", ['all']);
			
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
				
				$updateStatement->execute([
					$row['optionValue'],
					$row['optionID'],
					$row['groupID']
				]);
			}
			WCF::getDB()->commitTransaction();
			
			// clear user group option cache
			UserGroupEditor::resetCache();
		}
		
		return $bbCode;
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->objects as $bbcode) {
			$bbcode->update([
				'isDisabled' => $bbcode->isDisabled ? 0 : 1
			]);
		}
	}
}
