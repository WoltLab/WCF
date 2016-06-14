<?php
namespace wcf\system\log\modification;
use wcf\data\modification\log\ModificationLog;
use wcf\data\modification\log\ModificationLogEditor;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles modification logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Log\Modification
 * @deprecated	3.0, use AbstractModificationLogHandler
 */
class ModificationLogHandler extends SingletonFactory {
	/**
	 * list of object types
	 * @var	ObjectType[]
	 */
	protected $cache = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.modifiableContent');
	}
	
	/**
	 * Returns object type by object type name.
	 * 
	 * @param	string		$objectType
	 * @return	\wcf\data\object\type\ObjectType
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
	 * @return	ModificationLog
	 * @throws	SystemException
	 */
	protected function _add($objectType, $objectID, $action, array $additionalData = [], $time = TIME_NOW, $userID = null, $username = null) {
		$objectTypeObj = $this->getObjectType($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' not found within definition 'com.woltlab.wcf.modifiableContent'");
		}
		
		if ($userID === null) {
			if (WCF::getUser()->userID) {
				$userID = WCF::getUser()->userID;
			}
			else if ($username === null) {
				$username = 'System';
			}
		}
		if ($username === null) {
			if (WCF::getUser()->username) $username = WCF::getUser()->username;
			else $username = '';
		}
		
		return ModificationLogEditor::create([
			'objectTypeID' => $objectTypeObj->objectTypeID,
			'objectID' => $objectID,
			'action' => $action,
			'userID' => $userID,
			'username' => $username,
			'time' => $time,
			'additionalData' => serialize($additionalData)
		]);
	}
	
	/**
	 * Removes log entries.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 * @throws	SystemException
	 */
	protected function _remove($objectType, array $objectIDs) {
		$objectTypeObj = $this->getObjectType($objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Object type '".$objectType."' not found within definition 'com.woltlab.wcf.modifiableContent'");
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [$objectTypeObj->objectTypeID]);
		$conditions->add("objectID IN (?)", [$objectIDs]);
		
		$sql = "DELETE FROM	wcf".WCF_N."_modification_log
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
	}
}
