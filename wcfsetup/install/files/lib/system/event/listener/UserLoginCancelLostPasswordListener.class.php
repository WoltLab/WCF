<?php

namespace wcf\system\event\listener;

use wcf\data\user\UserAction;
use wcf\system\user\authentication\event\UserLoggedIn;

/**
 * Cancels lost password requests if the user successfully logs in.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event\Listener
 * @since   5.5
 */
final class UserLoginCancelLostPasswordListener
{
    public function __invoke(UserLoggedIn $event)
    {
        $user = $event->getUser();
        if (!$user->lostPasswordKey) {
            return;
        }

        (new UserAction([$user], 'cancelLostPasswordRequest'))->executeAction();
    }
}
