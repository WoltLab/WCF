<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for the state (banned, enabled) of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserStateCondition extends AbstractSingleFieldCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.state';
	
	/**
	 * true if the the user has to be banned
	 * @var	integer
	 */
	protected $userIsBanned = 0;
	
	/**
	 * true if the user has to be disabled
	 * @var	integer
	 */
	protected $userIsDisabled = 0;
	
	/**
	 * true if the user has to be enabled
	 * @var	integer
	 */
	protected $userIsEnabled = 0;
	
	/**
	 * true if the the user may not be banned
	 * @var	integer
	 */
	protected $userIsNotBanned = 0;
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if (isset($conditionData['userIsBanned'])) {
			$objectList->getConditionBuilder()->add('user_table.banned = ?', [$conditionData['userIsBanned']]);
		}
		
		if ($conditionData['userIsEnabled']) {
			if ($conditionData['userIsEnabled']) {
				$objectList->getConditionBuilder()->add('user_table.activationCode = ?', [0]);
			}
			else {
				$objectList->getConditionBuilder()->add('user_table.activationCode <> ?', [0]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		if ($condition->userIsBanned !== null && $user->banned != $condition->userIsBanned) {
			return false;
		}
		
		if ($condition->userIsEnabled !== null) {
			if ($condition->userIsEnabled && $user->activationCode) {
				return false;
			}
			else if (!$condition->userIsEnabled && !$user->activationCode) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		$data = [];
		
		if ($this->userIsBanned) {
			$data['userIsBanned'] = 1;
		}
		else if ($this->userIsNotBanned) {
			$data['userIsBanned'] = 0;
		}
		if ($this->userIsEnabled) {
			$data['userIsEnabled'] = 1;
		}
		else if ($this->userIsDisabled) {
			$data['userIsEnabled'] = 0;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * Returns the "checked" attribute for an input element.
	 * 
	 * @param	string		$propertyName
	 * @return	string
	 */
	protected function getCheckedAttribute($propertyName) {
		if ($this->$propertyName) {
			return ' checked="checked"';
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		$userIsNotBanned = WCF::getLanguage()->get('wcf.user.condition.state.isNotBanned');
		$userIsBanned = WCF::getLanguage()->get('wcf.user.condition.state.isBanned');
		$userIsDisabled = WCF::getLanguage()->get('wcf.user.condition.state.isDisabled');
		$userIsEnabled = WCF::getLanguage()->get('wcf.user.condition.state.isEnabled');
		
		return <<<HTML
<label><input type="checkbox" name="userIsBanned" value="1"{$this->getCheckedAttribute('userIsBanned')} /> {$userIsBanned}</label>
<label><input type="checkbox" name="userIsNotBanned" value="1"{$this->getCheckedAttribute('userIsNotBanned')} /> {$userIsNotBanned}</label>
<label><input type="checkbox" name="userIsEnabled" value="1"{$this->getCheckedAttribute('userIsEnabled')} /> {$userIsEnabled}</label>
<label><input type="checkbox" name="userIsDisabled" value="1"{$this->getCheckedAttribute('userIsDisabled')} /> {$userIsDisabled}</label>
HTML;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['userIsBanned'])) $this->userIsBanned = 1;
		if (isset($_POST['userIsDisabled'])) $this->userIsDisabled = 1;
		if (isset($_POST['userIsEnabled'])) $this->userIsEnabled = 1;
		if (isset($_POST['userIsNotBanned'])) $this->userIsNotBanned = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->userIsBanned = 0;
		$this->userIsDisabled = 0;
		$this->userIsEnabled = 0;
		$this->userIsNotBanned = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		if ($condition->userIsBanned !== null) {
			$this->userIsBanned = $condition->userIsBanned;
			$this->userIsNotBanned = !$condition->userIsBanned;
		}
		if ($condition->userIsEnabled !== null) {
			$this->userIsEnabled = $condition->userIsEnabled;
			$this->userIsDisabled = !$condition->userIsEnabled;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->userIsBanned && $this->userIsNotBanned) {
			$this->errorMessage = 'wcf.user.condition.state.isBanned.error.conflict';
			
			throw new UserInputException('userIsBanned', 'conflict');
		}
		
		if ($this->userIsDisabled && $this->userIsEnabled) {
			$this->errorMessage = 'wcf.user.condition.state.isEnabled.error.conflict';
			
			throw new UserInputException('userIsEnabled', 'conflict');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
