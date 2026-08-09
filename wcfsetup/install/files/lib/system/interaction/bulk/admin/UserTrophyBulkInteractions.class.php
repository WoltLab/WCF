<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyList;
use wcf\event\interaction\bulk\admin\UserTrophyBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for user trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
class UserTrophyBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_TROPHY === 0
            || !WCF::getSession()->getPermission('admin.trophy.canAwardTrophy')
        ) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction(
                'core/users/trophies/%s',
                static fn(UserTrophy $userTrophy) => !$userTrophy->getTrophy()->awardAutomatically
            ),
        ]);

        EventHandler::getInstance()->fire(
            new UserTrophyBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return UserTrophyList::class;
    }
}
