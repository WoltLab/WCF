<?php

namespace wcf\command\reaction\type;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeEditor;
use wcf\event\reaction\type\ReactionTypeEnabled;
use wcf\system\event\EventHandler;

/**
 * Enable a reaction type.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableReactionType
{
    public function __construct(private readonly ReactionType $reactionType) {}

    public function __invoke(): void
    {
        if ($this->reactionType->isAssignable) {
            return;
        }

        (new ReactionTypeEditor($this->reactionType))->update([
            'isAssignable' => 1,
        ]);

        $event = new ReactionTypeEnabled($this->reactionType);
        EventHandler::getInstance()->fire($event);
    }
}
