<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyList;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Condition implementation for trophies.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserTrophyCondition extends AbstractMultipleFieldsCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $descriptions = [
		'userTrophy' => 'wcf.user.condition.userTrophy.description',
		'notUserTrophy' => 'wcf.user.condition.notUserTrophy.description'
	];
	
	/**
	 * @inheritDoc
	 */
	protected $labels = [
		'userTrophy' => 'wcf.user.condition.userTrophy',
		'notUserTrophy' => 'wcf.user.condition.notUserTrophy'
	];
	
	/**
	 * ids of the selected trophies the user has earned
	 * @var	integer[]
	 */
	protected $userTrophy = [];
	
	/**
	 * ids of the selected trophies the user has not earned
	 * @var	integer[]
	 */
	protected $notUserTrophy = [];
	
	/**
	 * selectable trophies
	 * @var	Trophy[]
	 */
	protected $trophies;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if (isset($conditionData['userTrophy'])) {
			$objectList->getConditionBuilder()->add('user_table.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_trophy WHERE trophyID IN (?) GROUP BY userID HAVING COUNT(userID) = ?)', [$conditionData['trophyIDs'], count($conditionData['trophyIDs'])]);
		}
		if (isset($conditionData['notUserTrophy'])) {
			$objectList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_trophy WHERE trophyID IN (?))', [$conditionData['notTrophyIDs']]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		$trophies = UserTrophyList::getUserTrophies([$user->getObjectID()], false)[$user->getObjectID()];
		$trophyIDs = array_keys($trophies);
		
		if (!empty($condition->conditionData['userTrophy']) && !empty(array_diff($condition->conditionData['userTrophy'], $trophyIDs))) {
			return false;
		}
		
		if (!empty($condition->conditionData['notUserTrophy']) && !empty(array_intersect($condition->conditionData['notUserTrophy'], $trophyIDs))) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		$data = [];
		
		if (!empty($this->userTrophy)) {
			$data['userTrophy'] = $this->userTrophy;
		}
		if (!empty($this->notUserTrophy)) {
			$data['notUserTrophy'] = $this->notUserTrophy;
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
		if (!count($this->getTrophies())) {
			return '';
		}
		
		return <<<HTML
<dl{$this->getErrorClass('userTrophy')}>
	<dt>{$this->getLabel('userTrophy')}</dt>
	<dd>
		{$this->getOptionElements('userTrophy')}
		{$this->getDescriptionElement('userTrophy')}
		{$this->getErrorMessageElement('userTrophy')}
	</dd>
</dl>
<dl{$this->getErrorClass('notUserTrophy')}>
	<dt>{$this->getLabel('notUserTrophy')}</dt>
	<dd>
		{$this->getOptionElements('notUserTrophy')}
		{$this->getDescriptionElement('notUserTrophy')}
		{$this->getErrorMessageElement('notUserTrophy')}
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
		$trophies = $this->getTrophies();
		
		$returnValue = "";
		foreach ($trophies as $trophy) {
			/** @noinspection PhpVariableVariableInspection */
			$returnValue .= "<label><input type=\"checkbox\" name=\"".$identifier."[]\" value=\"".$trophy->trophyID."\"".(in_array($trophy->trophyID, $this->$identifier) ? ' checked' : "")."> ".$trophy->getTitle()."</label>";
		}
		
		return $returnValue;
	}
	
	/**
	 * Returns the selectable user groups.
	 *
	 * @return	Trophy[]
	 */
	protected function getTrophies() {
		if ($this->trophies == null) {
			$trophyList = new TrophyList();
			$trophyList->readObjects();
			$this->trophies = $trophyList->getObjects();
			
			uasort($this->trophies, function(Trophy $a, Trophy $b) {
				return strcmp($a->getTitle(), $b->getTitle());
			});
		}
		
		return $this->trophies;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['userTrophy'])) $this->userTrophy = ArrayUtil::toIntegerArray($_POST['userTrophy']);
		if (isset($_POST['notUserTrophy'])) $this->notUserTrophy = ArrayUtil::toIntegerArray($_POST['notUserTrophy']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->userTrophy = [];
		$this->notUserTrophy = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		if ($condition->userTrophy !== null) {
			$this->userTrophy = $condition->userTrophy;
		}
		if ($condition->notUserTrophy !== null) {
			$this->notUserTrophy = $condition->notUserTrophy;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$trophies = $this->getTrophies();
		foreach ($this->userTrophy as $trophyID) {
			if (!isset($trophies[$trophyID])) {
				$this->errorMessages['userTrophy'] = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('userTrophy', 'noValidSelection');
			}
		}
		foreach ($this->notUserTrophy as $trophyID) {
			if (!isset($trophies[$trophyID])) {
				$this->errorMessages['notUserTrophy'] = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('notUserTrophy', 'noValidSelection');
			}
		}
		
		if (count(array_intersect($this->notUserTrophy, $this->userTrophy))) {
			$this->errorMessages['notUserTrophy'] = 'wcf.user.condition.notUserTrophy.error.userTrophyIntersection';
			
			throw new UserInputException('notUserTrophy', 'userTrophyIntersection');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		return $this->checkUser($condition, WCF::getUser());
	}
}
