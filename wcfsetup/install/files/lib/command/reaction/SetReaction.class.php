<?php

namespace wcf\command\reaction;

use wcf\data\like\Like;
use wcf\data\like\LikeBuilder;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObjectBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\object\IReactionObject;
use wcf\data\reaction\type\ReactionType;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\reaction\ReactionSet;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Sets a reaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SetReaction
{
    public function __construct(
        private readonly ILikeObject $likeable,
        private readonly User $user,
        private readonly ReactionType $reactionType
    ) {}

    public function __invoke(): void
    {
        LikeObjectBuilder::createFromLikeable($this->likeable);

        try {
            WCF::getDB()->beginTransaction();

            $likeObject = LikeObjectBuilder::getLikeObjectForUpdate($this->likeable);

            $originalLike = Like::getLike(
                $this->likeable->getObjectType()->objectTypeID,
                $this->likeable->getObjectID(),
                $this->user->userID
            );

            if ($originalLike->likeID === 0) {
                // new reaction
                $like = LikeBuilder::forCreate()
                    ->setObjectID($this->likeable->getObjectID())
                    ->setObjectTypeID($this->likeable->getObjectType()->objectTypeID)
                    ->setObjectUserID($this->likeable->getUserID() ?: null)
                    ->setUserID($this->user->userID)
                    ->setTime(\TIME_NOW)
                    ->setLikeValue(Like::LIKE)
                    ->setReactionTypeID($this->reactionType->reactionTypeID)
                    ->create();

                $this->updateUserCounter($this->likeable, $like);

                $this->likeable->updateLikeCounter($likeObject->likes + 1);
            } else {
                // update existing reaction
                $like = LikeBuilder::forUpdate($originalLike)
                    ->setTime(\TIME_NOW)
                    ->setLikeValue(Like::LIKE)
                    ->setReactionTypeID($this->reactionType->reactionTypeID)
                    ->update();
            }

            LikeObjectBuilder::rebuildLikeObjectData([$likeObject->getObjectID()]);

            WCF::getDB()->commitTransaction();
        } catch (DatabaseQueryException $e) {
            WCF::getDB()->rollBackTransaction();

            throw $e;
        }

        $this->updateUserActivityEvent(
            $this->likeable,
            $this->user,
            $this->reactionType,
            $originalLike,
        );

        // This interface should help to determine whether the plugin has been adapted to the API 5.2.
        // If a LikeableObject does not implement this interface, no notification will be sent, because
        // we assume, that the plugin has not been adapted to the new API.
        if ($this->likeable instanceof IReactionObject) {
            $this->likeable->sendNotification($like);
        }

        EventHandler::getInstance()->fire(
            new ReactionSet($this->likeable, $this->user, $this->reactionType)
        );
    }

    /**
     * Updates the `likesReceived` counter of the likeable object's owner.
     */
    private function updateUserCounter(ILikeObject $likeable, Like $like): void
    {
        if ($likeable->getUserID() === null) {
            return;
        }

        UserActivityPointHandler::getInstance()->fireEvent(
            'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
            $like->likeID,
            $likeable->getUserID()
        );

        UserBuilder::forUpdate(UserRuntimeCache::getInstance()->getObject($likeable->getUserID()))
            ->incrementLikesReceived(1)
            ->update();
    }

    private function updateUserActivityEvent(
        ILikeObject $likeable,
        User $user,
        ReactionType $reactionType,
        Like $originalLike,
    ): void {
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType . '.recentActivityEvent') !== null) {
            $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.user.recentActivityEvent',
                $likeable->getObjectType()->objectType . '.recentActivityEvent'
            );

            if ($objectType->supportsReactions === '1') {
                if (!$originalLike->isNil()) {
                    UserActivityEventHandler::getInstance()->removeEvent(
                        $likeable->getObjectType()->objectType . '.recentActivityEvent',
                        $likeable->getObjectID(),
                        $user->userID
                    );
                }

                UserActivityEventHandler::getInstance()->fireEvent(
                    $likeable->getObjectType()->objectType . '.recentActivityEvent',
                    $likeable->getObjectID(),
                    $likeable->getLanguageID(),
                    $user->userID,
                    \TIME_NOW,
                    [
                        'reactionTypeID' => $reactionType->reactionTypeID,
                        /* @deprecated 6.1 use `reactionTypeID` */
                        'reactionType' => $reactionType,
                    ]
                );
            }
        }
    }
}
