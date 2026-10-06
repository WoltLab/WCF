<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;

/**
 * Completely disables the multi-factor authentication of a user by removing
 * all of their configured methods.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DisableUserMultifactor
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        // The user is updated directly instead of using `UpdateUser` to keep
        // event listeners from running inside the transaction.
        WCF::getDB()->beginTransaction();
        foreach (Setup::getAllForUser($this->user) as $setup) {
            $setup->delete();
        }

        (new UserEditor($this->user))->update([
            'multifactorActive' => 0,
        ]);
        WCF::getDB()->commitTransaction();
    }
}
