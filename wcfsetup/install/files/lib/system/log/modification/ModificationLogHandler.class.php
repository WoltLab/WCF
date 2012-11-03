<?php
namespace wcf\system\log\modification;
use wcf\data\modification\log\ModificationLogEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles modification logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.log.modification
 * @category	Community Framework
 */
class ModificationLogHandler extends SingletonFactory {
	/**
	 * list of object types
	 * @var	array<wcf\data\object\type\ObjectType>
	 */
	protected $cache = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.modifiableContent');
	}
	
	/**
	 * Returns object type by object type name.
	 * 
	 * @param	string		$objectType
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectType) {
		foreach ($this->cache as $objectTypeObj) {
			if ($objectTypeObj->objectType == $objectType) {
				return $objectTypeObj;
			}
		}
		
		return null;
	}
	
	/**
	 * Adds a new entry to modification log.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$action
	 * @param	array		$additionalData
	 * @param	integer		$time
	 * @param	integer		$userID
	 * @param	string		$username
	 * @return	wcf\data\modification\log\ModificationLog
	 */
	protected function _add($objectType, $objectID, $action, array $additionalData = array(), $time = TIME_NOW, $userID = null, $username = null) {
		$objectType = $this->getObjectType($objectType);
		if ($objectType === null) {
			throw new SystemException("Object type '".$objectType."' not found within definition 'com.woltlab.wcf.modifiableContent'");
		}
		
		return ModificationLogEditor::create(array(
			'objectTypeID' => $objectType->objectTypeID,
			'objectID' => $objectID,
			'action' => $action,
			'userID' => ($userID === null ? WCF::getUser()->userID : $userID),
			'username' => ($username === null ? WCF::getUser()->username : $username),
			'time' => $time,
			'additionalData' => serialize($additionalData)
		));
	}
	
	/**
	 * Removes log entries.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	protected function _remove($objectType, array $objectIDs) {
		$objectType = $this->getObjectType($objectType);
		if ($objectType === null) {
			throw new SystemException("Object type '".$objectType."' not found within definition 'com.woltlab.wcf.modifiableContent'");
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", array($objectType->objectTypeID));
		$conditions->add("objectID IN (?)", array($objectIDs));
		
		$sql = "DELETE FROM	wcf".WCF_N."_modification_log
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
}