<?php
namespace wcf\system\acl\simple;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Simplified ACL handlers that stores access data for objects requiring
 * just a simple yes/no instead of a set of different permissions. 
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Acl\Simple
 */
class SimpleAclHandler extends SingletonFactory {
	/**
	 * list of registered object types
	 * @var ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.acl.simple');
	}
	
	/**
	 * Returns the object type id by object type.
	 * 
	 * @param       string          $objectType     object type name
	 * @return      integer         object type id
	 * @throws      SystemException
	 */
	public function getObjectTypeID($objectType) {
		if (!isset($this->objectTypes[$objectType])) {
			throw new SystemException("Unknown object type '" . $objectType . "'");
		}
		
		return $this->objectTypes[$objectType]->objectTypeID;
	}
	
	/**
	 * Returns the user and group values for provided object type and object id.
	 * 
	 * @param       string          $objectType     object type name
	 * @param       integer         $objectID       object id
	 * @return      array           array containing the keys `allowAll`, `user` and `group`
	 */
	public function getValues($objectType, $objectID) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		$data = [
			'allowAll' => true,
			'user' => [],
			'group' => []
		];
		
		$sql = "SELECT  userID
			FROM    wcf".WCF_N."_acl_simple_to_user
			WHERE   objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectTypeID,
			$objectID
		]);
		$userIDs = [];
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		
		$sql = "SELECT  groupID
			FROM    wcf".WCF_N."_acl_simple_to_group
			WHERE   objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectTypeID,
			$objectID
		]);
		$groupIDs = [];
		while ($row = $statement->fetchArray()) {
			$groupIDs[] = $row['groupID'];
		}
		
		if (!empty($userIDs) || !empty($groupIDs)) {
			$data['allowAll'] = false;
			
			if (!empty($userIDs)) {
				$data['user'] = UserRuntimeCache::getInstance()->getObjects($userIDs);
			}
			
			if (!empty($groupIDs)) {
				$data['group'] = UserGroup::getGroupsByIDs($groupIDs);
			}
		}
		
		return $data;
	}
	
	/**
	 * Sets the user and group values for provided object type and object id.
	 * 
	 * @param	string		$objectType	object type name
	 * @param	integer		$objectID	object id
	 * @param	array		$values		list of user and group ids
	 * @throws	SystemException
	 */
	public function setValues($objectType, $objectID, array $values) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		// validate data of `$values`
		if (empty($values['user']) && empty($values['group']) && !isset($values['allowAll'])) {
			throw new SystemException("Missing ACL configuration values.");
		}
		
		// users
		$sql = "DELETE FROM     wcf".WCF_N."_acl_simple_to_user
			WHERE           objectTypeID = ?
					AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectTypeID,
			$objectID
		]);
		
		if ($values['allowAll'] == 0 && !empty($values['user'])) {
			$values['user'] = ArrayUtil::toIntegerArray($values['user']);
			if (!empty($values['user'])) {
				$sql = "INSERT INTO     wcf" . WCF_N . "_acl_simple_to_user
							(objectTypeID, objectID, userID)
					VALUES          (?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($values['user'] as $userID) {
					$statement->execute([
						$objectTypeID,
						$objectID,
						$userID
					]);
				}
				WCF::getDB()->commitTransaction();
			}
		}
		
		// groups
		$sql = "DELETE FROM     wcf".WCF_N."_acl_simple_to_group
			WHERE           objectTypeID = ?
					AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$objectTypeID,
			$objectID
		]);
		
		if ($values['allowAll'] == 0 && !empty($values['group'])) {
			$values['group'] = ArrayUtil::toIntegerArray($values['group']);
			if (!empty($values['group'])) {
				$sql = "INSERT INTO     wcf" . WCF_N . "_acl_simple_to_group
						(objectTypeID, objectID, groupID)
				VALUES          (?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($values['group'] as $groupID) {
					$statement->execute([
						$objectTypeID,
						$objectID,
						$groupID
					]);
				}
				WCF::getDB()->commitTransaction();
			}
		}
		
		// reset cache for object type
		SimpleAclResolver::getInstance()->resetCache($objectType);
	}
	
	/**
	 * Processes the provided values and returns the final
	 * values for template assignment.
	 * 
	 * @param       array   $rawValues      acl values as provided (by the user input)
	 * @return      array   final values for template assignment
	 */
	public function getOutputValues(array $rawValues) {
		$aclValues = [
			'allowAll' => true,
			'user' => [],
			'group' => []
		];
		
		if (isset($rawValues['allowAll']) && $rawValues['allowAll'] == 0) {
			if (!empty($rawValues['user'])) {
				$first = current($rawValues['user']);
				if ($first instanceof User) {
					$aclValues['user'] = $rawValues['user'];
				}
				else {
					$aclValues['user'] = UserRuntimeCache::getInstance()->getObjects($rawValues['user']);
				}
			}
			
			if (!empty($rawValues['group'])) {
				$first = current($rawValues['group']);
				if ($first instanceof UserGroup) {
					$aclValues['group'] = $rawValues['group'];
				}
				else {
					$aclValues['group'] = UserGroup::getGroupsByIDs($rawValues['group']);
				}
			}
			
			if (!empty($aclValues['user']) || !empty($aclValues['group'])) {
				$aclValues['allowAll'] = false;
			}
		}
		
		return $aclValues;
	}
}
