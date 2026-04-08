<?php

namespace wcf\system\user\notification\event;

use wcf\data\category\Category;
use wcf\data\category\CategoryAction;
use wcf\system\cache\eager\CategoryCache;
use wcf\system\category\CategoryHandler;
use wcf\system\user\notification\TestableUserNotificationEventHandler;

/**
 * Provides a method to create a category of a certain object type to be used by
 * categorized object user notification events.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TTestableCategorizedUserNotificationEvent
{
    /**
     * Returns a newly created test category of the given object type.
     *
     * @param mixed[] $additionalData
     * @return  Category
     */
    protected static function createTestCategory(string $objectTypeName, array $additionalData = [])
    {
        $objectType = CategoryHandler::getInstance()->getObjectTypeByName($objectTypeName);
        if ($objectType === null) {
            throw new \InvalidArgumentException("Unknown comment object type '{$objectTypeName}'.");
        }

        $category = (new CategoryAction([], 'create', [
            'data' => [
                'additionalData' => \serialize($additionalData),
                'description' => 'Category Description',
                'isDisabled' => 0,
                'objectTypeID' => $objectType->objectTypeID,
                'title' => 'Category Title',
            ],
        ]))->executeAction()['returnValues'];

        // work-around to reset category cache during this request
        TestableUserNotificationEventHandler::getInstance()->resetCacheHandler((new CategoryCache()));

        CategoryHandler::getInstance()->reloadCache();

        return $category;
    }
}
