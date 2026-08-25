<?php

namespace wcf\command\user\object\watch;

use wcf\data\object\type\ObjectType;
use wcf\data\user\object\watch\UserObjectWatch;
use wcf\data\user\object\watch\UserObjectWatchBuilder;
use wcf\data\user\User;
use wcf\event\user\object\watch\ObjectUnsubscribed;
use wcf\system\event\EventHandler;
use wcf\system\user\object\watch\IUserObjectWatch;

/**
 * Removes the subscription of a user to a watchable object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class Unsubscribe
{
    /**
     * @param ObjectType $objectType object type of the definition `com.woltlab.wcf.user.objectWatch`
     */
    public function __construct(
        private readonly ObjectType $objectType,
        private readonly int $objectID,
        private readonly User $user,
    ) {
        if (!($this->objectType->getProcessor() instanceof IUserObjectWatch)) {
            throw new \InvalidArgumentException('Given objectType is invalid.');
        }
    }

    public function __invoke(): void
    {
        $userObjectWatch = UserObjectWatch::getUserObjectWatch(
            $this->objectType->objectTypeID,
            $this->user->userID,
            $this->objectID
        );
        if ($userObjectWatch === null) {
            return;
        }

        UserObjectWatchBuilder::delete($userObjectWatch);

        $this->resetUserStorage();

        EventHandler::getInstance()->fire(
            new ObjectUnsubscribed($userObjectWatch)
        );
    }

    private function resetUserStorage(): void
    {
        $processor = $this->objectType->getProcessor();
        \assert($processor instanceof IUserObjectWatch);

        $processor->resetUserStorage([$this->user->userID]);
    }
}
