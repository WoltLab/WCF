<?php
namespace wcf\system\acl;
use wcf\data\acl\option\category\ACLOptionCategory;
use wcf\data\acl\option\category\ACLOptionCategoryList;
use wcf\data\acl\option\ACLOption;
use wcf\data\acl\option\ACLOptionList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\cache\builder\ACLOptionCategoryCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles ACL permissions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.acl
 * @category	Community Framework
 */
class ACLHandler extends SingletonFactory {
	/**
	 * indicates if assignment of variables is disabled
	 * @var	integer
	 */
	protected $assignVariablesDisabled = false;
	
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * list of acl option categories sorted by their object type id and name
	 * @var	array<array>
	 */
	protected $categories = array();
	
	/**
	 * Assignes the acl values to the template.
	 * 
	 * @param	integer		$objectTypeID
	 */
	public function assignVariables($objectTypeID) {
		if (WCF::getTPL()->get('aclValues') === null) {
			WCF::getTPL()->assign('aclValues', array());
		}
		
		if (!$this->assignVariablesDisabled && isset($_POST['aclValues'])) {
			$values = $_POST['aclValues'];
			
			$data = $this->getPermissions($objectTypeID, array(), null, true);
			
			foreach ($values as $type => $optionData) {
				if ($type === 'user') {
					$users = User::getUsers(array_keys($optionData));
				}
				
				$values[$type] = array(
					'label' => array(),
					'option' => array()
				);
				
				foreach ($optionData as $typeID => $optionValues) {
					foreach ($optionValues as $optionID => $optionValue) {
						if (!isset($data['options'][$optionID])) {
							unset($optionValues[$optionID]);
						}
					}
					
					if (empty($optionValues)) {
						continue;
					}
					
					$values[$type]['option'][$typeID] = $optionValues;
					
					if ($type === 'group') {
						$values[$type]['label'][$typeID] = UserGroup::getGroupByID($typeID)->getName();
					}
					else {
						$values[$type]['label'][$typeID] = $users[$typeID]->username;
					}
				}
			}
			
			$values['options'] = $data['options'];
			$values['categories'] = $data['categories'];
			
			WCF::getTPL()->append('aclValues', array(
				$objectTypeID => $values
			));
		}
	}
	
	/**
	 * Disables assignment of variables to template.
	 */
	public function disableAssignVariables() {
		$this->assignVariablesDisabled = true;
	}
	
	/**
	 * Enables assignment of variables to template.
	 */
	public function enableAssignVariables() {
		$this->assignVariablesDisabled = false;
	}
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.acl');
		$this->categories = ACLOptionCategoryCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Gets the object type id.
	 * 
	 * @param	string 		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType]->objectTypeID;
	}
	
	/**
	 * Returns the acl option category with the given object type id and name.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	string		$categoryName
	 * @return	wcf\data\acl\option\category\ACLOptionCategory
	 */
	public function getCategory($objectTypeID, $categoryName) {
		if (isset($this->categories[$objectTypeID][$categoryName])) {
			return $this->categories[$objectTypeID][$categoryName];
		}
		
		return null;
	}
	
	/**
	 * Saves acl for a given object.
	 * 
	 * @param	integer		$objectID
	 * @param	integer		$objectTypeID
	 */
	public function save($objectID, $objectTypeID) {
		// get options
		$optionList = ACLOption::getOptions($objectTypeID);
		
		$this->replaceValues($optionList, 'group', $objectID);
		$this->replaceValues($optionList, 'user', $objectID);
	}
	
	/**
	 * Replaces values for given type and object.
	 * 
	 * @param	wcf\data\acl\option\ACLOptionList	$optionList
	 * @param	string					$type
	 * @param	integer					$objectID
	 */
	protected function replaceValues(ACLOptionList $optionList, $type, $objectID) {
		$options = $optionList->getObjects();
		
		// remove previous values
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("optionID IN (?)", array(array_keys($options)));
		$conditions->add("objectID = ?", array($objectID));
		
		$sql = "DELETE FROM	wcf".WCF_N."_acl_option_to_".$type."
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// add new values if given
		if (!isset($_POST['aclValues']) || !isset($_POST['aclValues'][$type])) {
			return;
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_acl_option_to_".$type."
					(optionID, objectID, ".$type."ID, optionValue)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$values =& $_POST['aclValues'][$type];
		
		WCF::getDB()->beginTransaction();
		foreach ($values as $typeID => $optionData) {
			foreach ($optionData as $optionID => $optionValue) {
				// ignore invalid option ids
				if (!isset($options[$optionID])) {
					continue;
				}
				
				$statement->execute(array(
					$optionID,
					$objectID,
					$typeID,
					$optionValue
				));
			}
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Returns a list of permissions by object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	array		$objectIDs
	 * @param	string		$categoryName
	 * @param	boolean		$settingsView
	 * @return	array
	 */
	public function getPermissions($objectTypeID, array $objectIDs, $categoryName = '', $settingsView = false) {
		$optionList = $this->getOptions($objectTypeID, $categoryName);
		
		$data = array(
			'options' => $optionList,
			'group' => array(),
			'user' => array()
		);
		
		if (!empty($objectIDs)) {
			$this->getValues($optionList, 'group', $objectIDs, $data, $settingsView);
			$this->getValues($optionList, 'user', $objectIDs, $data, $settingsView);
		}
		
		// use alternative data structure for settings
		if ($settingsView) {
			$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
			
			$data['options'] = array();
			$data['categories'] = array();
			
			if (count($optionList)) {
				$categoryNames = array();
				foreach ($optionList as $option) {
					$data['options'][$option->optionID] = array(
						'categoryName' => $option->categoryName,
						'label' => WCF::getLanguage()->get('wcf.acl.option.'.$objectType->objectType.'.'.$option->optionName),
						'optionName' => $option->optionName
					);
					
					if (!in_array($option->categoryName, $categoryNames)) {
						$categoryNames[] = $option->categoryName;
					}
				}
				
				// load categories
				$categoryList = new ACLOptionCategoryList();
				$categoryList->getConditionBuilder()->add("acl_option_category.categoryName IN (?)", array($categoryNames));
				$categoryList->getConditionBuilder()->add("acl_option_category.objectTypeID = ?", array($objectTypeID));
				$categoryList->readObjects();
				
				foreach ($categoryList as $category) {
					$data['categories'][$category->categoryName] = WCF::getLanguage()->get('wcf.acl.option.category.'.$objectType->objectType.'.'.$category->categoryName);
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Fetches ACL option values by type.
	 * 
	 * @param	wcf\data\acl\option\ACLOptionList	$optionList
	 * @param	string					$type
	 * @param	array					$objectIDs
	 * @param	array					$data
	 * @param	boolean					$settingsView
	 */
	protected function getValues(ACLOptionList $optionList, $type, array $objectIDs, array &$data, $settingsView) {
		$data[$type] = array();
		$optionsIDs = array();
		foreach ($optionList as $option) {
			$optionsIDs[] = $option->optionID;
		}
		
		// category matched no options
		if (empty($optionsIDs)) {
			return;
		}
		
		$columnID = $type.'ID';
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("optionID IN (?)", array($optionsIDs));
		$conditions->add("objectID IN (?)", array($objectIDs));
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_acl_option_to_".$type."
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$type][$row['objectID']])) {
				$data[$type][$row['objectID']] = array();
			}
			
			if (!isset($data[$type][$row['objectID']][$row[$columnID]])) {
				$data[$type][$row['objectID']][$row[$columnID]] = array();
			}
			
			$data[$type][$row['objectID']][$row[$columnID]][$row['optionID']] = $row['optionValue'];
		}
		
		// use alternative data structure for settings
		if ($settingsView) {
			$objectID = current($objectIDs);
			if (!isset($data[$type][$objectID])) {
				$data[$type][$objectID] = array();
			}
			
			// build JS-compilant structure
			$data[$type] = array(
				'label' => array(),
				'option' => $data[$type][$objectID]
			);
			
			// load labels
			if (!empty($data[$type]['option'])) {
				$conditions = new PreparedStatementConditionBuilder();
				
				if ($type == 'group') {
					$conditions->add("groupID IN (?)", array(array_keys($data[$type]['option'])));
					$sql = "SELECT	groupID, groupName
						FROM	wcf".WCF_N."_user_group
						".$conditions;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditions->getParameters());
					
					while ($row = $statement->fetchArray()) {
						$data['group']['label'][$row['groupID']] = WCF::getLanguage()->get($row['groupName']);
					}
				}
				else {
					$conditions->add("userID IN (?)", array(array_keys($data[$type]['option'])));
					$sql = "SELECT	userID, username
						FROM	wcf".WCF_N."_user
						".$conditions;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditions->getParameters());
					
					while ($row = $statement->fetchArray()) {
						$data['user']['label'][$row['userID']] = $row['username'];
					}
				}
			}
		}
	}
	
	/**
	 * Returns a list of options by object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	string		$categoryName
	 * @return	wcf\data\acl\option\ACLOptionList
	 */
	public function getOptions($objectTypeID, $categoryName = '') {
		$optionList = new ACLOptionList();
		if (!empty($categoryName)) {
			if (StringUtil::endsWith($categoryName, '.*')) {
				$categoryName = StringUtil::substring($categoryName, 0, -1) . '%';
				$optionList->getConditionBuilder()->add("acl_option.categoryName LIKE ?", array($categoryName));
			}
			else {
				$optionList->getConditionBuilder()->add("acl_option.categoryName = ?", array($categoryName));
			}
		}
		$optionList->getConditionBuilder()->add("acl_option.objectTypeID = ?", array($objectTypeID));
		$optionList->readObjects();
		
		return $optionList;
	}
	
	/**
	 * Removes ACL values from database.
	 * 
	 * @param	integer						$objectTypeID
	 * @param	array<integer>					$objectIDs
	 * @param	wcf\data\acl\option\category\ACLOptionCategory	$category
	 */
	public function removeValues($objectTypeID, array $objectIDs, ACLOptionCategory $category = null) {
		$optionList = $this->getOptions($objectTypeID, $category);
		$options = $optionList->getObjects();
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("optionID IN (?)", array(array_keys($options)));
		$conditions->add("objectID IN (?)", array($objectIDs));
		
		WCF::getDB()->beginTransaction();
		foreach (array('group', 'user') as $type) {
			$sql = "DELETE FROM	wcf".WCF_N."_acl_option_to_".$type."
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
		WCF::getDB()->commitTransaction();
	}
}
