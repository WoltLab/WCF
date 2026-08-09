<?php

namespace wcf\system\interaction\admin;

use wcf\data\trophy\Trophy;
use wcf\event\interaction\admin\TrophyInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class TrophyInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_TROPHY === 0
            || !WCF::getSession()->getPermission('admin.trophy.canManageTrophy')
        ) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction('core/trophies/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new TrophyInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Trophy::class;
    }
}
