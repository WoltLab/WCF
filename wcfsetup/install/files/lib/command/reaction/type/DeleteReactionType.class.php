<?php

namespace wcf\command\reaction\type;

use wcf\command\file\DeleteFiles;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeBuilder;
use wcf\event\reaction\type\ReactionTypeDeleted;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Deletes a reaction type and closes the gap in the show order.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteReactionType
{
    public function __construct(
        private readonly ReactionType $reactionType,
    ) {}

    public function __invoke(): void
    {
        ReactionTypeBuilder::delete($this->reactionType);

        $sql = "UPDATE  wcf1_reaction_type
                SET     showOrder = showOrder - 1
                WHERE   showOrder > ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->reactionType->showOrder,
        ]);

        // The icon is deleted only after the reaction type, because deleting a file cannot be rolled back.
        if ($this->reactionType->iconFileID !== null) {
            $file = FileRuntimeCache::getInstance()->getObject($this->reactionType->iconFileID);
            if ($file !== null) {
                new DeleteFiles([$file])();
            }
        }

        ReactionTypeCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new ReactionTypeDeleted($this->reactionType));
    }
}
