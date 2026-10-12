<?php

namespace wcf\event\user;

use wcf\data\user\avatar\IUserAvatar;
use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that the avatar of a user without an uploaded avatar is being
 * loaded. Allows to provide a custom avatar, the default avatar is used if
 * no avatar is set.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserAvatarLoading implements IPsr14Event
{
    public function __construct(
        public readonly User $user,
        private ?IUserAvatar $avatar = null,
    ) {}

    public function getAvatar(): ?IUserAvatar
    {
        return $this->avatar;
    }

    public function setAvatar(?IUserAvatar $avatar): void
    {
        $this->avatar = $avatar;
    }
}
