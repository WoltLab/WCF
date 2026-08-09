<?php

namespace wcf\system\interaction\admin;

use wcf\data\reaction\type\ReactionType;
use wcf\event\interaction\admin\ReactionTypeInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for reaction types.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class ReactionTypeInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_LIKE === 0
            || !WCF::getSession()->getPermission('admin.content.reaction.canManageReactionType')
        ) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction('core/reactions/types/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new ReactionTypeInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return ReactionType::class;
    }
}
