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
    #[\Override]
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

    #[\Override]
    public function canAddWithoutApproval(int $objectID)
    {
        return true;
    }

    #[\Override]
    public function getLink(int $objectTypeID, int $objectID)
    {
        $entry = new ViewableModerationQueue(new ModerationQueue($objectID));

        return $entry->getLink();
    }

    #[\Override]
    public function getTitle(int $objectTypeID, int $objectID, bool $isResponse = false)
    {
        return '';
    }

    #[\Override]
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

    #[\Override]
    public function canAdd(int $objectID)
    {
        if (!$this->isAccessible($objectID, true)) {
            return false;
        }

        return true;
    }

    #[\Override]
    protected function canEdit(bool $isOwner)
    {
        return $isOwner;
    }

    #[\Override]
    protected function canDelete(bool $isOwner)
    {
        return $isOwner;
    }

    #[\Override]
    public function supportsLike()
    {
        return false;
    }

    #[\Override]
    public function supportsReport()
    {
        return false;
    }
}
