<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Condition implementation for the languages of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class UserLanguageCondition extends AbstractSingleFieldCondition implements IContentCondition, IObjectCondition, IObjectListCondition {
	use TObjectListUserCondition;
	use TObjectUserCondition;
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.languages';
	
	/**
	 * ids of the selected languages
	 * @var	integer[]
	 */
	protected $languageIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('user_table.languageID IN (?)', [$conditionData['languageIDs']]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkObject(DatabaseObject $object, array $conditionData) {
		if (!($object instanceof User) || ($object instanceof DatabaseObjectDecorator && !($object->getDecoratedObject() instanceof User))) {
			throw new \InvalidArgumentException("Object is no (decorated) instance of '".User::class."', instance of '".get_class($object)."' given.");
		}
		
		if (!empty($conditionData['languageIDs']) && !in_array($object->languageID, $conditionData['languageIDs'])) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		if (!empty($this->languageIDs)) {
			return [
				'languageIDs' => $this->languageIDs
			];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		$returnValue = "";
		foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
			$returnValue .= "<label><input type=\"checkbox\" name=\"languageIDs[]\" value=\"".$language->languageID."\"".(in_array($language->languageID, $this->languageIDs) ? ' checked' : "")."> ".$language->languageName."</label>";
		}
		
		return $returnValue;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->languageIDs = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		if (!empty($condition->conditionData['languageIDs'])) {
			$this->languageIDs = $condition->conditionData['languageIDs'];
		}
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		if (WCF::getUser()->userID) {
			return $this->checkObject(WCF::getUser(), $condition->conditionData);
		}
		
		if (!empty($condition->conditionData['languageIDs']) && !in_array(WCF::getLanguage()->languageID, $condition->conditionData['languageIDs'])) {
			return false;
		}
		
		return true;
	}
}
