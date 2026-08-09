<?php

namespace wcf\system\interaction\admin;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\event\interaction\admin\BBCodeMediaProviderInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for bb code media providers.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class BBCodeMediaProviderInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.content.bbcode.canManageBBCode')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction('core/bbcodes/media/providers/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new BBCodeMediaProviderInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return BBCodeMediaProvider::class;
    }
}
