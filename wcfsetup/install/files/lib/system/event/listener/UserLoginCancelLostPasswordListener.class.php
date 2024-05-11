<?php

namespace wcf\system\event\listener;

use wcf\data\user\UserAction;
use wcf\event\user\authentication\UserLoggedIn;

/**
 * Cancels lost password requests if the user successfully logs in.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
final class UserLoginCancelLostPasswordListener
{
    public function __invoke(UserLoggedIn $event): void
    {
        $user = $event->getUser();
        if (!$user->lostPasswordKey) {
            return;
        }

        (new UserAction([$user], 'cancelLostPasswordRequest'))->executeAction();
    }
}
