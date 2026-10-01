<?php

namespace wcf\command\reaction\type;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeBuilder;
use wcf\event\reaction\type\ReactionTypeUpdated;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a reaction type.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateReactionType
{
    public function __construct(
        private readonly ReactionTypeBuilder $builder,
    ) {}

    public function __invoke(): ReactionType
    {
        $reactionType = $this->builder->update();

        ReactionTypeCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new ReactionTypeUpdated(
            $reactionType,
            $this->builder
        ));

        return $reactionType;
    }
}
