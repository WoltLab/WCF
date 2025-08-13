<?php

namespace wcf\command\reaction\type;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeEditor;
use wcf\event\reaction\type\ReactionTypeDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a reaction type.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableReactionType
{
    public function __construct(private readonly ReactionType $reactionType) {}

    public function __invoke(): void
    {
        (new ReactionTypeEditor($this->reactionType))->update([
            'isAssignable' => 0,
        ]);

        $event = new ReactionTypeDisabled($this->reactionType);
        EventHandler::getInstance()->fire($event);
    }
}
