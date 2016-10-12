<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Condition implementation for all of the user groups a user has to be a member
 * of and the user groups a user may not be a member of.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserGroupCondition extends AbstractMultipleFieldsCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $descriptions = [
		'groupIDs' => 'wcf.user.condition.groupIDs.description',
		'notGroupIDs' => 'wcf.user.condition.notGroupIDs.description'
	];
	
	/**
	 * ids of the selected user groups the user has to be member of
	 * @var	integer[]
	 */
	protected $groupIDs = [];
	
	/**
	 * @inheritDoc
	 */
	protected $labels = [
		'groupIDs' => 'wcf.user.condition.groupIDs',
		'notGroupIDs' => 'wcf.user.condition.notGroupIDs'
	];
	
	/**
	 * ids of the selected user groups the user may not be member of
	 * @var	integer[]
	 */
	protected $notGroupIDs = [];
	
	/**
	 * selectable user groups
	 * @var	UserGroup[]
	 */
	protected $userGroups;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if (isset($conditionData['groupIDs'])) {
			$objectList->getConditionBuilder()->add('user_table.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (?) GROUP BY userID HAVING COUNT(userID) = ?)', [$conditionData['groupIDs'], count($conditionData['groupIDs'])]);
		}
		if (isset($conditionData['notGroupIDs'])) {
			$objectList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (?))', [$conditionData['notGroupIDs']]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		$groupIDs = $user->getGroupIDs();
		if (!empty($condition->conditionData['groupIDs']) && count(array_diff($condition->conditionData['groupIDs'], $groupIDs))) {
			return false;
		}
		
		if (!empty($condition->conditionData['notGroupIDs']) && count(array_intersect($condition->conditionData['notGroupIDs'], $groupIDs))) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		$data = [];
		
		if (!empty($this->groupIDs)) {
			$data['groupIDs'] = $this->groupIDs;
		}
		if (!empty($this->notGroupIDs)) {
			$data['notGroupIDs'] = $this->notGroupIDs;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		return <<<HTML
<dl{$this->getErrorClass('groupIDs')}>
	<dt>{$this->getLabel('groupIDs')}</dt>
	<dd>
		{$this->getOptionElements('groupIDs')}
		{$this->getDescriptionElement('groupIDs')}
		{$this->getErrorMessageElement('groupIDs')}
	</dd>
</dl>
<dl{$this->getErrorClass('notGroupIDs')}>
	<dt>{$this->getLabel('notGroupIDs')}</dt>
	<dd>
		{$this->getOptionElements('notGroupIDs')}
		{$this->getDescriptionElement('notGroupIDs')}
		{$this->getErrorMessageElement('notGroupIDs')}
	</dd>
</dl>
HTML;
	}
	
	/**
	 * Returns the option elements for the user group selection.
	 * 
	 * @param	string		$identifier
	 * @return	string
	 */
	protected function getOptionElements($identifier) {
		$userGroups = $this->getUserGroups();
		
		$returnValue = "";
		foreach ($userGroups as $userGroup) {
			/** @noinspection PhpVariableVariableInspection */
			$returnValue .= "<label><input type=\"checkbox\" name=\"".$identifier."[]\" value=\"".$userGroup->groupID."\"".(in_array($userGroup->groupID, $this->$identifier) ? ' checked' : "")."> ".$userGroup->getName()."</label>";
		}
		
		return $returnValue;
	}
	
	/**
	 * Returns the selectable user groups.
	 * 
	 * @return	UserGroup[]
	 */
	protected function getUserGroups() {
		if ($this->userGroups == null) {
			$invalidGroupTypes = [
				UserGroup::EVERYONE,
				UserGroup::USERS
			];
			if (!$this->includeguests) {
				$invalidGroupTypes[] = UserGroup::GUESTS;
			}
			
			$this->userGroups = UserGroup::getAccessibleGroups([], $invalidGroupTypes);
			
			uasort($this->userGroups, function(UserGroup $groupA, UserGroup $groupB) {
				return strcmp($groupA->getName(), $groupB->getName());
			});
		}
		
		return $this->userGroups;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['notGroupIDs'])) $this->notGroupIDs = ArrayUtil::toIntegerArray($_POST['notGroupIDs']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->groupIDs = [];
		$this->notGroupIDs = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		if ($condition->groupIDs !== null) {
			$this->groupIDs = $condition->groupIDs;
		}
		if ($condition->notGroupIDs !== null) {
			$this->notGroupIDs = $condition->notGroupIDs;
		}
	}
	
	/**
	 * Sets the selectable user groups.
	 * 
	 * @param	UserGroup[]	$userGroups
	 */
	public function setUserGroups(array $userGroups) {
		$this->userGroups = $userGroups;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$userGroups = $this->getUserGroups();
		foreach ($this->groupIDs as $groupID) {
			if (!isset($userGroups[$groupID])) {
				$this->errorMessages['groupIDs'] = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('groupIDs', 'noValidSelection');
			}
		}
		foreach ($this->notGroupIDs as $groupID) {
			if (!isset($userGroups[$groupID])) {
				$this->errorMessages['notGroupIDs'] = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('notGroupIDs', 'noValidSelection');
			}
		}
		
		if (count(array_intersect($this->notGroupIDs, $this->groupIDs))) {
			$this->errorMessages['notGroupIDs'] = 'wcf.user.condition.notGroupIDs.error.groupIDsIntersection';
			
			throw new UserInputException('notGroupIDs', 'groupIDsIntersection');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		return $this->checkUser($condition, WCF::getUser());
	}
}
