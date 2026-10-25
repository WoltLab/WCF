<?php

namespace wcf\command\reaction;

use wcf\data\like\object\LikeObject;
use wcf\data\like\object\LikeObjectBuilder;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Copies all reactions of an object to another object, for example when copying a post.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CopyObjectReactions
{
    public function __construct(
        private readonly string $sourceObjectType,
        private readonly int $sourceObjectID,
        private readonly string $targetObjectType,
        private readonly int $targetObjectID,
    ) {}

    public function __invoke(): void
    {
        $sourceObjectType = ReactionHandler::getInstance()->getObjectType($this->sourceObjectType);
        if ($sourceObjectType === null) {
            throw new \InvalidArgumentException('Given sourceObjectType is invalid.');
        }

        $targetObjectType = ReactionHandler::getInstance()->getObjectType($this->targetObjectType);
        if ($targetObjectType === null) {
            throw new \InvalidArgumentException('Given targetObjectType is invalid.');
        }

        $sql = "SELECT  *
                FROM    wcf1_like_object
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $sourceObjectType->objectTypeID,
            $this->sourceObjectID,
        ]);
        $sourceLikeObject = $statement->fetchSingleObject(LikeObject::class);

        // no reactions at all
        if ($sourceLikeObject === null) {
            return;
        }

        $likeObject = LikeObjectBuilder::forCreate()
            ->setObjectTypeID($targetObjectType->objectTypeID)
            ->setObjectID($this->targetObjectID)
            ->setObjectUserID($sourceLikeObject->objectUserID)
            ->setLikes($sourceLikeObject->likes)
            ->setCachedReactions($sourceLikeObject->getCachedReactions())
            ->create();

        $sql = "INSERT INTO wcf1_like
                            (objectID, objectTypeID, objectUserID, userID, time, likeValue, reactionTypeID)
                SELECT      ?, ?, objectUserID, userID, time, likeValue, reactionTypeID
                FROM        wcf1_like
                WHERE       objectTypeID = ?
                        AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->targetObjectID,
            $targetObjectType->objectTypeID,
            $sourceObjectType->objectTypeID,
            $this->sourceObjectID,
        ]);

        if ($likeObject->objectUserID !== null) {
            $this->updateUserCounter($likeObject);
        }
    }

    /**
     * Updates the `likesReceived` counter and the activity points of the liked object's owner.
     */
    private function updateUserCounter(LikeObject $likeObject): void
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_like
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $likeObject->objectTypeID,
            $likeObject->objectID,
        ]);
        $count = (int)$statement->fetchSingleColumn();

        if ($count === 0) {
            return;
        }

        UserBuilder::forUpdate(new User(null, ['userID' => $likeObject->objectUserID]))
            ->incrementLikesReceived($count)
            ->update();

        UserActivityPointHandler::getInstance()->fireEvents(
            'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
            [$likeObject->objectUserID => $count]
        );
    }
}
