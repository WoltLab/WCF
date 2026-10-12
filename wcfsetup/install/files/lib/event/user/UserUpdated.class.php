<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user has been updated.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserUpdated implements IPsr14Event
{
    public function __construct(
        public readonly User $user,
        public readonly UserBuilder $builder,
    ) {}
}
