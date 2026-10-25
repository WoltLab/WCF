<?php

namespace wcf\system\interaction\user;

use wcf\data\user\UserProfile;
use wcf\system\interaction\RpcInteraction;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Extended interaction provider for the context menu of user profiles with the option to follow users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserProfileInteractionsWithFollow extends UserProfileInteractions
{
    public function __construct()
    {
        $this->addInteractions([
            new RpcInteraction(
                'follow',
                'core/users/%s/follow',
                'wcf.user.button.follow',
                isAvailableCallback: static fn(UserProfile $user) => self::canFollow($user)
                    && !UserProfileHandler::getInstance()->isFollowing($user->userID),
            ),
            new RpcInteraction(
                'unfollow',
                'core/users/%s/unfollow',
                'wcf.user.button.unfollow',
                isAvailableCallback: static fn(UserProfile $user) => self::canFollow($user)
                    && UserProfileHandler::getInstance()->isFollowing($user->userID),
            ),
        ]);

        parent::__construct();
    }

    private static function canFollow(UserProfile $user): bool
    {
        return !WCF::getUser()->isGuest()
            && WCF::getUser()->userID !== $user->userID
            && !UserProfileHandler::getInstance()->isIgnoredByUser($user->userID);
    }
}
