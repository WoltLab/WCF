<?php

namespace wcf\command\user\trophy;

use wcf\command\user\SetSpecialTrophies;
use wcf\command\user\UpdateUser;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyBuilder;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\UserBuilder;
use wcf\event\user\trophy\UserTrophyCreated;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\UserTrophyNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Awards a trophy to a user.
 *
 * Updates the trophy points and the special trophies of the user and notifies
 * the user about the new trophy.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUserTrophy
{
    public function __construct(
        private readonly UserTrophyBuilder $builder,
    ) {}

    public function __invoke(): UserTrophy
    {
        $userTrophy = $this->builder->create();

        if (!$userTrophy->getTrophy()->isDisabled()) {
            (new UpdateUser(
                UserBuilder::forUpdate($userTrophy->getUserProfile()->getDecoratedObject())->incrementTrophyPoints(1)
            ))();

            $this->addSpecialTrophy($userTrophy);
        }

        UserActivityEventHandler::getInstance()->fireEvent(
            'com.woltlab.wcf.userTrophy.recentActivityEvent.trophyReceived',
            $userTrophy->getObjectID(),
            null,
            $userTrophy->userID
        );

        if ($userTrophy->userID !== WCF::getUser()->userID) {
            UserNotificationHandler::getInstance()->fireEvent(
                'received',
                'com.woltlab.wcf.userTrophy.notification',
                new UserTrophyNotificationObject($userTrophy),
                [
                    $userTrophy->userID,
                ]
            );
        }

        EventHandler::getInstance()->fire(new UserTrophyCreated($userTrophy, $this->builder));

        return $userTrophy;
    }

    /**
     * Adds the trophy to the special trophies of the user if the user has not
     * received it before and still has space for another special trophy.
     */
    private function addSpecialTrophy(UserTrophy $userTrophy): void
    {
        $userProfile = $userTrophy->getUserProfile();
        $specialTrophies = $userProfile->getSpecialTrophies();
        if (\count($specialTrophies) >= $userProfile->getPermission('user.profile.trophy.maxUserSpecialTrophies')) {
            return;
        }

        foreach (UserTrophyList::getUserTrophies([$userProfile->userID])[$userProfile->userID] as $existingUserTrophy) {
            if (
                $existingUserTrophy->trophyID === $userTrophy->trophyID
                && $existingUserTrophy->userTrophyID !== $userTrophy->userTrophyID
            ) {
                return;
            }
        }

        $trophyIDs = \array_map(
            static fn($trophy) => $trophy->trophyID,
            $specialTrophies
        );
        $trophyIDs[] = $userTrophy->trophyID;

        (new SetSpecialTrophies(
            $userProfile->getDecoratedObject(),
            \array_values(\array_unique($trophyIDs))
        ))();
    }
}
