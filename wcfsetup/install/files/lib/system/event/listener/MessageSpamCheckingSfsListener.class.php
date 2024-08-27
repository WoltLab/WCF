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

        // Skip spam check for team members and trusted users.
        if ($event->user !== null) {
            if ($this->isTeamMember($event->user->userID)) {
                return;
            }

            if ($this->isTrustedUser($event->user->userID)) {
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

    private function isTeamMember(int $userID): bool
    {
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($userID);
        if (
            $userProfile->getPermission('admin.general.canUseAcp')
            || $userProfile->getPermission('mod.general.canUseModeration')
        ) {
            return true;
        }

        return false;
    }

    private function isTrustedUser(int $userID): bool
    {
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($userID);
        if (
            $userProfile->activityPoints >= 100 ||
            $userProfile->registrationDate < \TIME_NOW - 86_400 * 180
        ) {
            return true;
        }

        return false;
    }
}
