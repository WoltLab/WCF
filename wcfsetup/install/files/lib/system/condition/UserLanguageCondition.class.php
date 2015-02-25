<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Condition implementation for the languages of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserLanguageCondition extends AbstractSingleFieldCondition implements IContentCondition, IUserCondition {
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $label = 'wcf.user.condition.languages';
	
	/**
	 * ids of the selected languages
	 * @var	array<integer>
	 */
	protected $languageIDs = array();
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		$userList->getConditionBuilder()->add('user_table.languageID IN (?)', array($condition->conditionData['languageIDs']));
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		if (!empty($condition->conditionData['languageIDs']) && !in_array($user->languageID, $condition->languageIDs)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if (!empty($this->languageIDs)) {
			return array(
				'languageIDs' => $this->languageIDs
			);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::getFieldElement()
	 */
	protected function getFieldElement() {
		$returnValue = "";
		foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
			$returnValue .= "<label><input type=\"checkbox\" name=\"languageIDs[]\" value=\"".$language->languageID."\"".(in_array($language->languageID, $this->languageIDs) ? ' checked="checked"' : "")." /> ".$language->languageName."</label>";
		}
		
		return $returnValue;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->languageIDs = array();
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		if (!empty($condition->conditionData['languageIDs'])) {
			$this->languageIDs = $condition->conditionData['languageIDs'];
		}
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		foreach ($this->languageIDs as $languageID) {
			if (LanguageFactory::getInstance()->getLanguage($languageID) === null) {
				$this->errorMessage = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('languageIDs', 'noValidSelection');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		return $this->checkUser($condition, WCF::getUser());
	}
}
