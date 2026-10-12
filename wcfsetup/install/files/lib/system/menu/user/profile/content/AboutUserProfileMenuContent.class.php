<?php

namespace wcf\system\menu\user\profile\content;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\view\user\profile\UserProfileAboutView;
use wcf\system\WCF;

/**
 * Handles user profile information content.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class AboutUserProfileMenuContent implements IUserProfileMenuContent
{
    #[\Override]
    public function getContent(int $userID): string
    {
        $user = UserProfileRuntimeCache::getInstance()->getObject($userID);
        if ($user === null) {
            return '';
        }

        return WCF::getTPL()->render('wcf', 'userProfileAbout', [
            'view' => new UserProfileAboutView($user),
            'userID' => $user->userID,
            'user' => $user,
        ]);
    }

    #[\Override]
    public function isVisible(int $userID): bool
    {
        return true;
    }
}
