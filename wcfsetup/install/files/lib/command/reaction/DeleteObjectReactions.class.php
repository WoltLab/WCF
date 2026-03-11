<?php

namespace wcf\command\reaction;

use wcf\data\like\LikeEditor;
use wcf\data\like\LikeList;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\like\object\LikeObjectList;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Deletes all reactions for specific objects, typically when they have been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteObjectReactions
{
    /**
     * @param int[] $objectIDs
     * @param string[] $notificationObjectTypes
     */
    public function __construct(
        private readonly string $objectType,
        private readonly array $objectIDs,
        private readonly array $notificationObjectTypes = []
    ) {}

    public function __invoke(): void
    {
        $objectTypeObj = ReactionHandler::getInstance()->getObjectType($this->objectType);
        if ($objectTypeObj === null) {
            throw new \InvalidArgumentException('Given objectType is invalid.');
        }

        // get like objects
        $likeObjectList = new LikeObjectList();
        $likeObjectList->getConditionBuilder()->add('like_object.objectTypeID = ?', [$objectTypeObj->objectTypeID]);
        $likeObjectList->getConditionBuilder()->add('like_object.objectID IN (?)', [$this->objectIDs]);
        $likeObjectList->readObjects();
        $likeObjects = $likeObjectList->getObjects();
        $likeObjectIDs = $likeObjectList->getObjectIDs();

        // reduce count of received users
        $users = [];
        foreach ($likeObjects as $likeObject) {
            if ($likeObject->likes && $likeObject->objectUserID) {
                if (!isset($users[$likeObject->objectUserID])) {
                    $users[$likeObject->objectUserID] = 0;
                }

                $users[$likeObject->objectUserID] -= $likeObject->likes;
            }
        }

        foreach ($users as $userID => $reactionData) {
            $userEditor = new UserEditor(new User(null, ['userID' => $userID]));
            $userEditor->updateCounters([
                'likesReceived' => $reactionData,
            ]);
        }

        // get like ids
        $likeList = new LikeList();
        $likeList->getConditionBuilder()->add('like_table.objectTypeID = ?', [$objectTypeObj->objectTypeID]);
        $likeList->getConditionBuilder()->add('like_table.objectID IN (?)', [$this->objectIDs]);
        $likeList->readObjects();

        if (\count($likeList)) {
            $activityPoints = $likeData = [];
            foreach ($likeList as $like) {
                $likeData[$like->likeID] = $like->userID;

                if ($like->objectUserID) {
                    if (!isset($activityPoints[$like->objectUserID])) {
                        $activityPoints[$like->objectUserID] = 0;
                    }
                    $activityPoints[$like->objectUserID]++;
                }
            }

            // delete like notifications
            if ($this->notificationObjectTypes !== []) {
                foreach ($this->notificationObjectTypes as $notificationObjectType) {
                    UserNotificationHandler::getInstance()
                        ->removeNotifications($notificationObjectType, $likeList->getObjectIDs());
                }
            } elseif (UserNotificationHandler::getInstance()->getObjectTypeID($this->objectType . '.notification')) {
                UserNotificationHandler::getInstance()
                    ->removeNotifications($this->objectType . '.notification', $likeList->getObjectIDs());
            }

            // revoke activity points
            UserActivityPointHandler::getInstance()
                ->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $activityPoints);

            // delete likes
            LikeEditor::deleteAll(\array_keys($likeData));
        }

        // delete like objects
        if ($likeObjectIDs !== []) {
            LikeObjectEditor::deleteAll($likeObjectIDs);
        }

        // delete activity events
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType . '.recentActivityEvent')) {
            UserActivityEventHandler::getInstance()
                ->removeEvents($objectTypeObj->objectType . '.recentActivityEvent', $this->objectIDs);
        }
    }
}
