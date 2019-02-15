<?php
namespace wcf\system\user\notification\event;
use wcf\data\category\Category;
use wcf\data\category\CategoryAction;
use wcf\system\cache\builder\CategoryCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\user\notification\TestableUserNotificationEventHandler;

/**
 * Provides a method to create a category of a certain object type to be used by
 * categorized object user notification events.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 */
trait TTestableCategorizedUserNotificationEvent {
	/**
	 * Returns a newly created test category of the given object type.
	 * 
	 * @param	string		$objectTypeName
	 * @param	array		$additionalData
	 * @return	Category
	 */
	protected static function createTestCategory($objectTypeName, array $additionalData = []) {
		$objectType = CategoryHandler::getInstance()->getObjectTypeByName($objectTypeName);
		if ($objectType === null) {
			throw new \InvalidArgumentException("Unknown comment object type '{$objectTypeName}'.");
		}
		
		$category = (new CategoryAction([], 'create', [
			'data' => [
				'additionalData' => serialize($additionalData),
				'description' => 'Category Description',
				'isDisabled' => 0,
				'objectTypeID' => $objectType->objectTypeID,
				'title' => 'Category Title'
			]
		]))->executeAction()['returnValues'];
		
		// work-around to reset category cache during this request
		TestableUserNotificationEventHandler::getInstance()->resetCacheBuilder(CategoryCacheBuilder::getInstance());
		
		CategoryHandler::getInstance()->reloadCache();
		
		return $category;
	}
}
