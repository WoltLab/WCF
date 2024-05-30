<?php

namespace wcf\system\event\listener;

use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\event\message\MessageSpamChecking;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Checks for spam messages using data from Stop Forum Spam.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class MessageSpamCheckingSfsListener
{
    public function __invoke(MessageSpamChecking $event): void
    {
        if (!\BLACKLIST_SFS_ENABLE) {
            return;
        }

        if ($event->user !== null) {
            // Skip spam check for admins and moderators
            $userProfile = UserProfileRuntimeCache::getInstance()->getObject($event->user->userID);
            if (
                $userProfile->getPermission('admin.general.canUseAcp')
                || $userProfile->getPermission('mod.general.canUseModeration')
            ) {
                return;
            }
        }

        if (BlacklistEntry::getMatches(
            $event->user ? $event->user->username : '',
            $event->user ? $event->user->email : '',
            $event->ipAddress,
        ) !== []) {
            $event->preventDefault();
        }
    }
}
