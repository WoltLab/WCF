<?php

namespace wcf\system\user\command;

use wcf\data\user\User;

/**
 * Saves that a user is unfollowing another user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\user\Unfollow` instead.
 */
final class Unfollow
{
    public function __construct(
        private readonly User $user,
        private readonly User $target,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\user\Unfollow(
            $this->user,
            $this->target
        ))();
    }
}
