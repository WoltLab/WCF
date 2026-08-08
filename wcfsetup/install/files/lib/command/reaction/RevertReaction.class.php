<?php

namespace wcf\command\reaction;

use wcf\data\like\Like;
use wcf\data\like\LikeEditor;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\event\reaction\ReactionReverted;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Reverts a reaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class RevertReaction
{
    public function __construct(
        private readonly Like $like,
        private readonly ILikeObject $likeable,
    ) {}

    public function __invoke(): void
    {
        LikeObjectEditor::createFromLikeable($this->likeable);

        try {
            WCF::getDB()->beginTransaction();

            $likeObject = LikeObjectEditor::getLikeObjectForUpdate($this->likeable);

            (new LikeEditor($this->like))->delete();

            $this->updateUserCounter($this->likeable);

            $this->likeable->updateLikeCounter($likeObject->likes - 1);

            LikeObjectEditor::rebuildLikeObjectData([$likeObject->getObjectID()]);

            WCF::getDB()->commitTransaction();
        } catch (DatabaseQueryException $e) {
            WCF::getDB()->rollBackTransaction();

            throw $e;
        }

        $this->deleteUserActivityEvent(
            $this->likeable,
            UserRuntimeCache::getInstance()->getObject($this->like->userID)
        );

        EventHandler::getInstance()->fire(
            new ReactionReverted($this->like, $this->likeable)
        );
    }

    /**
     * Updates the `likesReceived` counter of the likeable object's owner.
     */
    private function updateUserCounter(ILikeObject $likeable): void
    {
        if ($likeable->getUserID() === null) {
            return;
        }

        UserActivityPointHandler::getInstance()->removeEvents(
            'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
            [$likeable->getUserID() => 1]
        );

        $userEditor = new UserEditor(UserRuntimeCache::getInstance()->getObject($likeable->getUserID()));
        $userEditor->updateCounters(['likesReceived' => -1]);
    }

    private function deleteUserActivityEvent(
        ILikeObject $likeable,
        User $user,
    ): void {
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType . '.recentActivityEvent') !== null) {
            UserActivityEventHandler::getInstance()->removeEvent(
                $likeable->getObjectType()->objectType . '.recentActivityEvent',
                $likeable->getObjectID(),
                $user->userID
            );
        }
    }
}
