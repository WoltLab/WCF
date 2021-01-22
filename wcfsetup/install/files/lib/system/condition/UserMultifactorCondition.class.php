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
 * Condition implementation for the multi-factor status of users.
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
	 * 1 if multifactor active checkbox is checked
	 * @var	int
	 */
	protected $multifactorActive = 0;
	
	/**
	 * 1 if multifactor not active checkbox is checked
	 * @var	int
	 */
	protected $multifactorNotActive = 0;
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		if ($this->multifactorActive || $this->multifactorNotActive) {
			return [
				// if multifactorNotActive is selected multifactorActive is 0
				// otherwise multifactorNotActive is 1
				'multifactorActive' => $this->multifactorActive
			];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFieldElement() {
		$multifactorActiveLabel = WCF::getLanguage()->get('wcf.user.condition.multifactor.multifactorActive');
		$multifactorNotActiveLabel = WCF::getLanguage()->get('wcf.user.condition.multifactor.multifactorNotActive');
		$multifactorActiveChecked = '';
		if ($this->multifactorActive) {
			$multifactorActiveChecked = ' checked';
		}
		
		$multifactorNotActiveChecked = '';
		if ($this->multifactorNotActive) {
			$multifactorNotActiveChecked = ' checked';
		}
		
		return <<<HTML
<label><input type="checkbox" name="multifactorActive" id="multifactorActive"{$multifactorActiveChecked}> {$multifactorActiveLabel}</label>
<label><input type="checkbox" name="multifactorNotActive" id="multifactorNotActive"{$multifactorNotActiveChecked}> {$multifactorNotActiveLabel}</label>
HTML;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['multifactorActive'])) $this->multifactorActive = 1;
		if (isset($_POST['multifactorNotActive'])) $this->multifactorNotActive = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->multifactorActive = $this->multifactorNotActive = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		$this->multifactorActive = $condition->multifactorActive;
		$this->multifactorNotActive = !$condition->multifactorActive;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->multifactorActive && $this->multifactorNotActive) {
			$this->errorMessage = 'wcf.user.condition.multifactor.multifactorActive.error.conflict';
			
			throw new UserInputException('multifactorActive', 'conflict');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new InvalidObjectArgument($objectList, UserList::class, 'Object list');
		}
		
		if (isset($conditionData['multifactorActive'])) {
			$objectList->getConditionBuilder()->add('user_table.multifactorActive = ?', [$conditionData['multifactorActive']]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		if ($condition->multifactorActive !== null && $user->multifactorActive != $condition->multifactorActive) {
			return false;
		}
		
		return true;
	}
}
