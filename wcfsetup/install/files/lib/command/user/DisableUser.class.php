<?php

namespace wcf\command\user;

use ParagonIE\ConstantTime\Hex;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\event\user\UserDisabled;
use wcf\system\event\EventHandler;
use wcf\util\UserRegistrationUtil;

/**
 * Disable a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableUser
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        $this->resetActivationCode($this->user);
        $this->addUserToGuestGroup($this->user);

        $event = new UserDisabled($this->user);
        EventHandler::getInstance()->fire($event);
    }

    private function addUserToGuestGroup(User $user): void
    {
        (new UserAction([$user], 'addToGroups', [
            'groups' => UserGroup::getGroupIDsByType([UserGroup::GUESTS]),
            'deleteOldGroups' => true,
            'addDefaultGroups' => false,
        ]))->executeAction();
    }

    private function resetActivationCode(User $user): void
    {
        // We reset the activationCode (which indicates, that the user is not enabled) AND disable the email
        // confirm status, because if the user can enable himself by an email confirmation and we do not reset
        // the email confirmed status, the behavior is undefined, because a user exists, which is not enabled
        // but has a valid email address (Which doesn't usually happen).
        (new UserEditor($user))->update([
            'activationCode' => UserRegistrationUtil::getActivationCode(),
            'emailConfirmed' => Hex::encode(\random_bytes(20)),
        ]);
    }
}
