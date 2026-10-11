<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\user\UserCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUser
{
    public function __construct(
        private readonly UserBuilder $builder,
    ) {}

    public function __invoke(): User
    {
        // The object returned by the builder lacks the user options.
        $user = new User($this->builder->create()->userID);

        if (\MODULE_USER_RANK !== 0) {
            (new RefreshUserRanks([$user]))();
        }
        (new RefreshUserOnlineMarkings([$user]))();

        EventHandler::getInstance()->fire(new UserCreated($user, $this->builder));

        return $user;
    }
}
