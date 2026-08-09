<?php

namespace wcf\system\interaction\admin;

use wcf\data\smiley\Smiley;
use wcf\event\interaction\admin\SmileyInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for smilies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class SmileyInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_SMILEY === 0
            || !WCF::getSession()->getPermission('admin.content.smiley.canManageSmiley')
        ) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction("core/smilies/%s")
        ]);

        EventHandler::getInstance()->fire(
            new SmileyInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Smiley::class;
    }
}
