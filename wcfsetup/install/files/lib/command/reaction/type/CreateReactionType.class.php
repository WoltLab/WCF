<?php

namespace wcf\command\reaction\type;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeBuilder;
use wcf\event\reaction\type\ReactionTypeCreated;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Creates a reaction type.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateReactionType
{
    public function __construct(
        private readonly ReactionTypeBuilder $builder,
    ) {}

    public function __invoke(): ReactionType
    {
        $reactionType = $this->builder->create();

        ReactionTypeCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new ReactionTypeCreated(
            $reactionType,
            $this->builder
        ));

        return $reactionType;
    }
}
