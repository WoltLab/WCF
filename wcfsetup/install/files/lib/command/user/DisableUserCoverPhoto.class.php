<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Disables the cover photo of a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DisableUserCoverPhoto
{
    public function __construct(
        private readonly User $user,
        private readonly string $reason,
        /** timestamp at which the cover photo is enabled again, `0` disables the cover photo permanently */
        private readonly int $expires = 0,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setDisableCoverPhoto(true)
            ->setDisableCoverPhotoReason($this->reason)
            ->setDisableCoverPhotoExpires(\min($this->expires, 2147483647));

        return (new UpdateUser($builder))();
    }
}
