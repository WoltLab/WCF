<?php

namespace wcf\system\interaction\user;

use wcf\action\PollEditAction;
use wcf\data\poll\Poll;
use wcf\event\interaction\user\PollInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\request\LinkHandler;

/**
 * Interaction provider for standalone polls.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new FormBuilderDialogInteraction(
                'edit',
                LinkHandler::getInstance()->getControllerLink(PollEditAction::class, ['id' => '%s']),
                'wcf.global.button.edit',
                static fn(Poll $poll) => $poll->canManage()
            ),
            new DeleteInteraction(
                'core/polls/%s',
                static fn(Poll $poll) => $poll->canManage()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new PollInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Poll::class;
    }
}
