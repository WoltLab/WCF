<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Enables the previously disabled cover photo of a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class EnableUserCoverPhoto
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setDisableCoverPhoto(false)
            ->setDisableCoverPhotoExpires(0);

        return (new UpdateUser($builder))();
    }
}
