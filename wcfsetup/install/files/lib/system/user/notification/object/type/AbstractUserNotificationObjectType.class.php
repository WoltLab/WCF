<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\data\DatabaseObjectList;

/**
 * Provides a default implementation of IUserNotificationObjectType.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 */
class AbstractUserNotificationObjectType extends AbstractObjectTypeProcessor implements IUserNotificationObjectType {
	/**
	 * class name of the object decorator
	 * @var	string
	 */
	protected static $decoratorClassName = '';
	
	/**
	 * object class name
	 * @var	string
	 */
	protected static $objectClassName = '';
	
	/**
	 * class name for DatabaseObjectList
	 * @var	string
	 */
	protected static $objectListClassName = '';
	
	/**
	 * @inheritDoc
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$indexName = call_user_func([static::$objectClassName, 'getDatabaseTableIndexName']);
		
		/** @var DatabaseObjectList $objectList */
		$objectList = new static::$objectListClassName();
		$objectList->setObjectIDs($objectIDs);
		$objectList->sqlLimit = 0;
		$objectList->decoratorClassName = static::$decoratorClassName;
		$objectList->readObjects();
		$objects = $objectList->getObjects();
		
		foreach ($objectIDs as $objectID) {
			// append empty objects for unknown ids
			if (!isset($objects[$objectID])) {
				// '__unknownNotificationObject' tells the notification API
				// that the object does not exist anymore so that the related
				// notification can be deleted automatically
				$objects[$objectID] = new static::$decoratorClassName(new static::$objectClassName(null, [
					'__unknownNotificationObject' => true,
					$indexName => $objectID
				]));
			}
		}
		
		return $objects;
	}
}
