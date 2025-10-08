<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserAction;

/**
 * Sets the preferred color scheme of a user.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SetColorScheme
{
    public function __construct(
        private readonly User $user,
        private readonly string $colorScheme
    ) {}

    public function __invoke(): void
    {
        $userAction = new UserAction([$this->user], 'update', [
            'options' => [
                User::getUserOptionID('colorScheme') => $this->colorScheme,
            ],
        ]);
        $userAction->executeAction();
    }
}
