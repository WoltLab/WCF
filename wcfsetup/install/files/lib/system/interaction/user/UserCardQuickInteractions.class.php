<?php

namespace wcf\system\interaction\user;

use wcf\data\user\UserProfile;
use wcf\event\interaction\user\UserCardQuickInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\user\UserFollowInteraction;

/**
 * Interaction provider for the quick interaction of user cards.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserCardQuickInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new UserFollowInteraction(),
            new UserIgnoreInteraction(),
        ]);

        EventHandler::getInstance()->fire(
            new UserCardQuickInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserProfile::class;
    }
}
