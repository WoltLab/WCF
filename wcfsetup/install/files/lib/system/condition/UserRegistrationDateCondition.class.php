<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Condition implementation for the registration date of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserRegistrationDateCondition extends AbstractSingleFieldCondition implements IContentCondition, IObjectListCondition, IUserCondition {
	use TObjectListUserCondition;
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $label = 'wcf.user.condition.registrationDate';
	
	/**
	 * registration start date
	 * @var	string
	 */
	protected $registrationDateEnd = '';
	
	/**
	 * registration start date
	 * @var	string
	 */
	protected $registrationDateStart = '';
	
	/**
	 * @see	\wcf\system\condition\IObjectListCondition::addObjectListCondition()
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) return;
		
		if (isset($conditionData['registrationDateEnd'])) {
			$userList->getConditionBuilder()->add('user_table.registrationDate < ?', array(strtotime($conditionData['registrationDateEnd']) + 86400));
		}
		if (isset($conditionData['registrationDateStart'])) {
			$userList->getConditionBuilder()->add('user_table.registrationDate >= ?', array(strtotime($conditionData['registrationDateStart'])));
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		if ($condition->registrationDateStart !== null && $user->registrationDate < strtotime($condition->registrationDateStart)) {
			return false;
		}
		if ($condition->registrationDateEnd !== null && $user->registrationDate >= strtotime($condition->registrationDateEnd) + 86400) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		$data = array();
		
		if (strlen($this->registrationDateStart)) {
			$data['registrationDateStart'] = $this->registrationDateStart;
		}
		if (strlen($this->registrationDateEnd)) {
			$data['registrationDateEnd'] = $this->registrationDateEnd;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::getFieldElement()
	 */
	protected function getFieldElement() {
		$start = WCF::getLanguage()->get('wcf.date.period.start');
		$end = WCF::getLanguage()->get('wcf.date.period.end');
		
		return <<<HTML
<input type="date" id="registrationDateStart" name="registrationDateStart" value="{$this->registrationDateStart}" placeholder="{$start}" />
<input type="date" id="registrationDateEnd" name="registrationDateEnd" value="{$this->registrationDateEnd}" placeholder="{$end}" />
HTML;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['registrationDateEnd'])) $this->registrationDateEnd = $_POST['registrationDateEnd'];
		if (isset($_POST['registrationDateStart'])) $this->registrationDateStart = $_POST['registrationDateStart'];
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->registrationDateEnd = '';
		$this->registrationDateStart = '';
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		if ($condition->registrationDateEnd) {
			$this->registrationDateEnd = $condition->registrationDateEnd;
		}
		if ($condition->registrationDateStart) {
			$this->registrationDateStart = $condition->registrationDateStart;
		}
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		$registrationDateEnd = $registrationDateStart = null;
		if (strlen($this->registrationDateStart)) {
			$registrationDateStart = @strtotime($this->registrationDateStart);
			if ($registrationDateStart === false) {
				$this->errorMessage = 'wcf.condition.timestamp.error.startNotValid';
				
				throw new UserInputException('registrationDate', 'startNotValid');
			}
		}
		if (strlen($this->registrationDateEnd)) {
			$registrationDateEnd = @strtotime($this->registrationDateEnd);
			if ($registrationDateEnd === false) {
				$this->errorMessage = 'wcf.condition.timestamp.error.endNotValid';
				
				throw new UserInputException('registrationDate', 'endNotValid');
			}
		}
		
		if ($registrationDateEnd !== null && $registrationDateStart !== null && $registrationDateEnd < $registrationDateStart) {
			$this->errorMessage = 'wcf.condition.timestamp.error.endBeforeStart';
			
			throw new UserInputException('registrationDate', 'endBeforeStart');
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
