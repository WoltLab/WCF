<?php

namespace wcf\command\user\object\watch;

use wcf\data\object\type\ObjectType;
use wcf\data\user\object\watch\UserObjectWatch;
use wcf\data\user\object\watch\UserObjectWatchBuilder;
use wcf\data\user\User;
use wcf\event\user\object\watch\ObjectSubscribed;
use wcf\system\event\EventHandler;
use wcf\system\user\object\watch\IUserObjectWatch;

/**
 * Subscribes a user to a watchable object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class Subscribe
{
    /**
     * @param ObjectType $objectType object type of the definition `com.woltlab.wcf.user.objectWatch`
     */
    public function __construct(
        private readonly ObjectType $objectType,
        private readonly int $objectID,
        private readonly User $user,
        private readonly bool $enableNotification = false,
    ) {
        if (!($this->objectType->getProcessor() instanceof IUserObjectWatch)) {
            throw new \InvalidArgumentException('Given objectType is invalid.');
        }
    }

    public function __invoke(): UserObjectWatch
    {
        $userObjectWatch = $this->getSubscription();
        $isNewSubscription = false;

        if ($userObjectWatch === null) {
            $userObjectWatch = UserObjectWatchBuilder::forCreate()
                ->setObjectType($this->objectType)
                ->setObjectID($this->objectID)
                ->setUser($this->user)
                ->setNotification($this->enableNotification)
                ->createOrIgnore();

            if ($userObjectWatch === null) {
                // The subscription has been created by a concurrent request.
                $userObjectWatch = $this->getSubscription();
                \assert($userObjectWatch !== null);
            } else {
                $isNewSubscription = true;
            }
        } elseif ((bool)$userObjectWatch->notification !== $this->enableNotification) {
            $userObjectWatch = UserObjectWatchBuilder::forUpdate($userObjectWatch)
                ->setNotification($this->enableNotification)
                ->update();
        }

        $this->resetUserStorage();

        if ($isNewSubscription) {
            EventHandler::getInstance()->fire(
                new ObjectSubscribed($userObjectWatch)
            );
        }

        return $userObjectWatch;
    }

    private function getSubscription(): ?UserObjectWatch
    {
        return UserObjectWatch::getUserObjectWatch(
            $this->objectType->objectTypeID,
            $this->user->userID,
            $this->objectID
        );
    }

    private function resetUserStorage(): void
    {
        $processor = $this->objectType->getProcessor();
        \assert($processor instanceof IUserObjectWatch);

        $processor->resetUserStorage([$this->user->userID]);
    }
}
