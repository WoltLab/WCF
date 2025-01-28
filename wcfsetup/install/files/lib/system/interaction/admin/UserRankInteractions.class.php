<?php

namespace wcf\system\interaction\admin;

use wcf\data\user\rank\UserRank;
use wcf\event\interaction\admin\UserRankInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

/**
 * Interaction provider for user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserRankInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction('core/users/ranks/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new UserRankInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserRank::class;
    }
}
