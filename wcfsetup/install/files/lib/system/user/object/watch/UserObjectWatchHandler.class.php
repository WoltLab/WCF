<?php

namespace wcf\system\user\object\watch;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Handles watched objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserObjectWatchHandler extends SingletonFactory
{
    /**
     * Returns the id of the given object type.
     *
     * @param string $objectTypeName
     * @return  int
     * @throws  SystemException
     */
    public function getObjectTypeID($objectTypeName)
    {
        $objectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectTypeName);
        if ($objectType === null) {
            throw new SystemException("unknown object type '" . $objectTypeName . "'");
        }

        return $objectType->objectTypeID;
    }

    /**
     * @inheritDoc
     */
    public function resetObject($objectType, $objectID)
    {
        $this->resetObjects($objectType, [$objectID]);
    }

    /**
     * Resets the object watch cache for all subscriber of the given object.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     */
    public function resetObjects($objectType, array $objectIDs)
    {
        // get object type id
        $objectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);

        // get subscriber
        $conditionsBuilder = new PreparedStatementConditionBuilder();
        $conditionsBuilder->add('objectTypeID = ?', [$objectTypeObj->objectTypeID]);
        $conditionsBuilder->add('objectID IN (?)', [$objectIDs]);
        $sql = "SELECT  userID
                FROM    wcf1_user_object_watch
                " . $conditionsBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionsBuilder->getParameters());
        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($userIDs)) {
            // reset user storage
            $objectTypeObj->getProcessor()->resetUserStorage($userIDs);
        }
    }

    /**
     * Deletes the given objects.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     * @param int[] $userIDs
     */
    public function deleteObjects($objectType, array $objectIDs, array $userIDs = [])
    {
        // get object type id
        $objectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);

        // delete objects
        $conditionsBuilder = new PreparedStatementConditionBuilder();
        $conditionsBuilder->add('objectTypeID = ?', [$objectTypeObj->objectTypeID]);
        $conditionsBuilder->add('objectID IN (?)', [$objectIDs]);
        if (!empty($userIDs)) {
            $conditionsBuilder->add('userID IN (?)', [$userIDs]);
        }

        $sql = "DELETE FROM wcf1_user_object_watch
                " . $conditionsBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionsBuilder->getParameters());
    }

    /**
     * Updates a watched object for all subscriber.
     *
     * @param string $objectType
     * @param int $objectID
     * @param string $notificationEventName
     * @param string $notificationObjectType
     * @param IUserNotificationObject $notificationObject
     * @param array $additionalData
     */
    public function updateObject(
        $objectType,
        $objectID,
        $notificationEventName,
        $notificationObjectType,
        IUserNotificationObject $notificationObject,
        array $additionalData = []
    ) {
        $objectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
        $userIDs = $this->getSubscribers($objectType, $objectID);

        if (!empty($userIDs)) {
            // reset user storage
            $objectTypeObj->getProcessor()->resetUserStorage(\array_keys($userIDs));

            $recipientIDs = \array_filter(
                $userIDs,
                static function ($notification, $userID) use ($notificationObject) {
                    return $notification && $userID != $notificationObject->getAuthorID();
                },
                \ARRAY_FILTER_USE_BOTH
            );

            if (!empty($recipientIDs)) {
                // create notifications
                UserNotificationHandler::getInstance()->fireEvent(
                    $notificationEventName,
                    $notificationObjectType,
                    $notificationObject,
                    \array_keys($recipientIDs),
                    $additionalData
                );
            }
        }
    }

    /**
     * Returns the subscribers for a specific object as an array.
     * The array key indicates the userID and the array value indicates
     * the notification status (`1` = should get a notification,
     * `0` = should not get a notification).
     *
     * @since 5.5
     */
    public function getSubscribers(string $objectType, int $objectID): array
    {
        $objectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);

        $sql = "SELECT  userID, notification
                FROM    wcf1_user_object_watch
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectTypeObj->objectTypeID, $objectID]);

        return $statement->fetchMap('userID', 'notification');
    }

    /**
     * Updates a watched object for all subscribers including subscribers
     * of the parent object.
     *
     * @since 5.5
     */
    public function updateObjectWithParent(
        string $objectType,
        int $objectID,
        string $parentObjectType,
        int $parentObjectID,
        string $notificationEventName,
        string $notificationObjectType,
        IUserNotificationObject $notificationObject,
        array $additionalData = []
    ) {
        $recipientIDs = [];
        $filterRecipients = static function ($notification, $userID) use ($notificationObject) {
            return $notification && $userID != $notificationObject->getAuthorID();
        };

        $objectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
        $userIDs = $this->getSubscribers($objectType, $objectID);
        if (!empty($userIDs)) {
            $objectTypeObj->getProcessor()->resetUserStorage(\array_keys($userIDs));
            $recipientIDs = \array_keys(
                \array_filter(
                    $userIDs,
                    $filterRecipients,
                    \ARRAY_FILTER_USE_BOTH
                )
            );
        }

        $parentObjectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $parentObjectType);
        $parentUserIDs = $this->getSubscribers($parentObjectType, $parentObjectID);
        if (!empty($parentUserIDs)) {
            $parentObjectTypeObj->getProcessor()->resetUserStorage(\array_keys($parentUserIDs));
            $parentRecipientIDs = \array_keys(
                \array_filter(
                    $parentUserIDs,
                    $filterRecipients,
                    \ARRAY_FILTER_USE_BOTH
                )
            );
            $recipientIDs = \array_unique(\array_merge($recipientIDs, $parentRecipientIDs));
        }

        if (!empty($recipientIDs)) {
            UserNotificationHandler::getInstance()->fireEvent(
                $notificationEventName,
                $notificationObjectType,
                $notificationObject,
                $recipientIDs,
                $additionalData
            );
        }
    }
}
