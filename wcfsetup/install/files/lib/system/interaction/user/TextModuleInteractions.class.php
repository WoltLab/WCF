<?php

namespace wcf\system\interaction\user;

use wcf\action\TextModuleEditAction;
use wcf\data\text\module\TextModule;
use wcf\event\interaction\user\TextModuleInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\request\LinkHandler;

/**
 * Interaction provider for text modules.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TextModuleInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new FormBuilderDialogInteraction(
                'edit',
                LinkHandler::getInstance()->getControllerLink(TextModuleEditAction::class, ['id' => '%s']),
                'wcf.global.button.edit',
                static fn() => TextModule::canUse()
            ),
            new DeleteInteraction(
                'core/text-modules/%s',
                static fn() => TextModule::canUse()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new TextModuleInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return TextModule::class;
    }
}
