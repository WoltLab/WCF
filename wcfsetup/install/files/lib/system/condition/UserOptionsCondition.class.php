<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\data\DatabaseObject;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\WCF;

/**
 * Condition implementation for the options of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserOptionsCondition extends AbstractMultipleFieldsCondition implements IContentCondition, IUserCondition {
	/**
	 * user option handler object
	 * @var	\wcf\system\option\user\UserOptionHandler
	 */
	protected $optionHandler = null;
	
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::__construct()
	 */
	public function __construct(DatabaseObject $object) {
		parent::__construct($object);
		
		$this->optionHandler = new UserOptionHandler(false);
		$this->optionHandler->enableConditionMode();
		$this->optionHandler->init();
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		$optionValues = $condition->optionValues;
		
		foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
			$option = $option['object'];
			
			if (isset($optionValues[$option->optionName])) {
				$this->optionHandler->getTypeObject($option->optionType)->addCondition($userList, $option, $optionValues[$option->optionName]);
			}
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		$optionValues = $condition->optionValues;
		
		$checkSuccess = true;
		foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
			$option = $option['object'];
			
			if (isset($optionValues[$option->optionName])) {
				if (!$this->optionHandler->getTypeObject($option->optionType)->checkUser($user, $option, $optionValues[$option->optionName])) {
					$checkSuccess = false;
					break;
				}
			}
		}
		
		return $checkSuccess;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		$optionValues = $this->optionHandler->getOptionValues();
		
		$data = array();
		foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
			$option = $option['object'];
			
			if (isset($optionValues[$option->optionName])) {
				$conditionData = $this->optionHandler->getTypeObject($option->optionType)->getConditionData($option, $optionValues[$option->optionName]);
				if ($conditionData !== null) {
					$data[$option->optionName] = $conditionData;
				}
			}
		}
		
		if (!empty($data)) {
			return array(
				'optionValues' => $data
			);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getHTML()
	 */
	public function getHTML() {
		return WCF::getTPL()->fetch('userOptionsCondition', 'wcf', array(
			'optionTree' => $this->optionHandler->getOptionTree('profile')
		));
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		$this->optionHandler->readUserInput($_POST);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->optionHandler->setOptionValues(array());
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		$this->optionHandler->setOptionValues($condition->conditionData['optionValues']);
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
