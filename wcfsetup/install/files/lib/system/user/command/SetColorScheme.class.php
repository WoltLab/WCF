<?php

namespace wcf\system\user\command;

use wcf\data\user\User;

/**
 * Sets the preferred color scheme of a user.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 * @deprecated  6.2 Use `\wcf\command\user\SetColorScheme` instead.
 */
final class SetColorScheme
{
    public function __construct(private readonly User $user, private readonly string $colorScheme) {}

    public function __invoke(): void
    {
        (new \wcf\command\user\SetColorScheme(
            $this->user,
            $this->colorScheme
        ))();
    }
}
