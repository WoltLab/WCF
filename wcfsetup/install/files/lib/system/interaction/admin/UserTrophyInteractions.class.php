<?php

namespace wcf\system\interaction\admin;

use wcf\data\user\trophy\UserTrophy;
use wcf\event\interaction\admin\UserTrophyInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

/**
 * Interaction provider for user trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class UserTrophyInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction(
                'core/users/trophies/%s',
                static fn(UserTrophy $userTrophy) => $userTrophy->getTrophy()->awardAutomatically === 0
            )
        ]);

        EventHandler::getInstance()->fire(
            new UserTrophyInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserTrophy::class;
    }
}
