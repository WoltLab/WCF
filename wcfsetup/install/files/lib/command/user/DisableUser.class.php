<?php

namespace wcf\command\user;

use ParagonIE\ConstantTime\Hex;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\util\UserRegistrationUtil;

/**
 * Disables a user and moves it from the users group into the guests group.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DisableUser
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): User
    {
        $builder = UserBuilder::forUpdate($this->user)
            ->setActivationCode(UserRegistrationUtil::getActivationCode())
            ->setGroupIDs($this->getGroupIDs());

        // A disabled user with a confirmed email address could not be enabled
        // through the email confirmation if users can activate themselves.
        if (((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_ADMIN) === 0) {
            $builder->setEmailConfirmed(Hex::encode(\random_bytes(20)));
        }

        return (new UpdateUser($builder))();
    }

    /**
     * @return list<int>
     */
    private function getGroupIDs(): array
    {
        $groupIDs = \array_diff(
            $this->user->getGroupIDs(true),
            UserGroup::getGroupIDsByType([UserGroup::USERS])
        );

        return \array_values(\array_unique(\array_merge(
            $groupIDs,
            UserGroup::getGroupIDsByType([UserGroup::GUESTS])
        )));
    }
}
