<?php

namespace wcf\system\comment\manager;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\user\UserProfile;

/**
 * Moderation queue comment manager implementation.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ModerationQueueCommentManager extends AbstractCommentManager implements ICommentPermissionManager
{
    /**
     * @inheritDoc
     */
    public function isAccessible(int $objectID, bool $validateWritePermission = false)
    {
        $entry = new ModerationQueue($objectID);

        return $entry->canEdit();
    }

    #[\Override]
    public function canModerateObject(int $objectTypeID, int $objectID, UserProfile $user): bool
    {
        $entry = new ModerationQueue($objectID);

        return $entry->canEdit($user->getDecoratedObject());
    }

    /**
     * @inheritDoc
     */
    public function canAddWithoutApproval(int $objectID)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getLink(int $objectTypeID, int $objectID)
    {
        $entry = new ViewableModerationQueue(new ModerationQueue($objectID));

        return $entry->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(int $objectTypeID, int $objectID, bool $isResponse = false)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function updateCounter(int $objectID, int $value)
    {
        $entry = new ModerationQueue($objectID);
        $editor = new ModerationQueueEditor($entry);
        $editor->updateCounters([
            'comments' => $value,
        ]);
        $editor->update([
            'lastChangeTime' => TIME_NOW,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function canAdd(int $objectID)
    {
        if (!$this->isAccessible($objectID, true)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function canEdit(bool $isOwner)
    {
        return $isOwner;
    }

    /**
     * @inheritDoc
     */
    protected function canDelete(bool $isOwner)
    {
        return $isOwner;
    }

    /**
     * @inheritDoc
     */
    public function supportsLike()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function supportsReport()
    {
        return false;
    }
}
