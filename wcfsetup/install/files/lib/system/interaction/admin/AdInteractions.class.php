<?php

namespace wcf\system\interaction\admin;

use wcf\data\ad\Ad;
use wcf\event\interaction\admin\AdInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for ads.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class AdInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_WCF_AD === 0
            || !WCF::getSession()->getPermission('admin.ad.canManageAd')
        ) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction("core/ads/%s")
        ]);

        EventHandler::getInstance()->fire(
            new AdInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Ad::class;
    }
}
