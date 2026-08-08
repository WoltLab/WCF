<?php

namespace wcf\command\reaction;

use wcf\data\like\Like;
use wcf\data\like\LikeEditor;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\object\IReactionObject;
use wcf\data\reaction\type\ReactionType;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
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
        LikeObjectEditor::createFromLikeable($this->likeable);

        try {
            WCF::getDB()->beginTransaction();

            $likeObject = LikeObjectEditor::getLikeObjectForUpdate($this->likeable);

            $originalLike = Like::getLike(
                $this->likeable->getObjectType()->objectTypeID,
                $this->likeable->getObjectID(),
                $this->user->userID
            );

            if ($originalLike->likeID === 0) {
                // new reaction
                $like = LikeEditor::create([
                    'objectID' => $this->likeable->getObjectID(),
                    'objectTypeID' => $this->likeable->getObjectType()->objectTypeID,
                    'objectUserID' => $this->likeable->getUserID() ?: null,
                    'userID' => $this->user->userID,
                    'time' => \TIME_NOW,
                    'likeValue' => 1,
                    'reactionTypeID' => $this->reactionType->reactionTypeID,
                ]);

                $this->updateUserCounter($this->likeable, $like);

                $this->likeable->updateLikeCounter($likeObject->likes + 1);
            } else {
                // update existing reaction
                $editor = new LikeEditor($originalLike);
                $editor->update([
                    'time' => \TIME_NOW,
                    'likeValue' => 1,
                    'reactionTypeID' => $this->reactionType->reactionTypeID,
                ]);

                // reload like object to avoid stale object (reaction type id)
                $like = new Like($originalLike->likeID);
            }

            LikeObjectEditor::rebuildLikeObjectData([$likeObject->getObjectID()]);

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

        $userEditor = new UserEditor(UserRuntimeCache::getInstance()->getObject($likeable->getUserID()));
        $userEditor->updateCounters(['likesReceived' => 1]);
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

            if ($objectType->supportsReactions) {
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
