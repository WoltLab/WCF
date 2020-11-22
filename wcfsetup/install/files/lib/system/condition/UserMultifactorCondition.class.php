<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\InvalidObjectArgument;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation if it is the user has an active second factor.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 * @since       5.4
 */
class UserMultifactorCondition extends AbstractSingleFieldCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.multifactor';
	
	/**
	 * 1 if uses multifactor checkbox is checked
	 * @var	integer
	 */
	protected $usesMultifactor = 0;
	
	/**
	 * 1 if uses no multifactor checkbox is checked
	 * @var	integer
	 */
	protected $usesNoMultifactor = 0;
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		if ($this->usesMultifactor || $this->usesNoMultifactor) {
			return [
				// if usesNoMultifactor is selected usesMultifactor is 0
				// otherwise usesNoMultifactor is 1
				'usesMultifactor' => $this->usesMultifactor
			];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFieldElement() {
		$usesMultifactorLabel = WCF::getLanguage()->get('wcf.user.condition.multifactor.usesMultifactor');
		$usesNoMultifactorLabel = WCF::getLanguage()->get('wcf.user.condition.multifactor.usesNoMultifactor');
		$usesMultifactorChecked = '';
		if ($this->usesMultifactor) {
			$usesMultifactorChecked = ' checked';
		}
		
		$usesNoMultifactorChecked = '';
		if ($this->usesNoMultifactor) {
			$usesNoMultifactorChecked = ' checked';
		}
		
		return <<<HTML
<label><input type="checkbox" name="usesMultifactor" id="usesMultifactor"{$usesMultifactorChecked}> {$usesMultifactorLabel}</label>
<label><input type="checkbox" name="usesNoMultifactor" id="usesNoMultifactor"{$usesNoMultifactorChecked}> {$usesNoMultifactorLabel}</label>
HTML;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['usesMultifactor'])) $this->usesMultifactor = 1;
		if (isset($_POST['usesNoMultifactor'])) $this->usesNoMultifactor = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->usesMultifactor = $this->usesNoMultifactor = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		$this->usesMultifactor = $condition->usesMultifactor;
		$this->usesNoMultifactor = !$condition->usesMultifactor;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->usesMultifactor && $this->usesNoMultifactor) {
			$this->errorMessage = 'wcf.user.condition.multifactor.usesMultifactor.error.conflict';
			
			throw new UserInputException('usesMultifactor', 'conflict');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		return (($condition->usesMultifactor && WCF::getUser()->multifactorActive) || (!$condition->usesMultifactor && !WCF::getUser()->multifactorActive));
	}
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
		}
		
		if (isset($conditionData['usesMultifactor'])) {
			$objectList->getConditionBuilder()->add('user_table.multifactorActive = ?', [$conditionData['usesMultifactor']]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		if ($condition->usesMultifactor !== null && $user->multifactorActive != $condition->usesMultifactor) {
			return false;
		}
		
		return true;
	}
}
