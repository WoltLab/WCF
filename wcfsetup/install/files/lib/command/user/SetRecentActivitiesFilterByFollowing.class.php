<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Sets whether the recent activities shown to a user are filtered by the users
 * they follow.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SetRecentActivitiesFilterByFollowing
{
    public function __construct(
        private readonly User $user,
        private readonly bool $filterByFollowing,
    ) {}

    public function __invoke(): void
    {
        new UpdateUser(
            UserBuilder::forUpdate($this->user)->setUserOption(
                User::getUserOptionID('recentActivitiesFilterByFollowing'),
                $this->filterByFollowing ? '1' : '0'
            )
        )();
    }
}
