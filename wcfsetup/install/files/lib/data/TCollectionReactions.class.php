<?php

namespace wcf\data;

use wcf\system\reaction\ReactionData;
use wcf\system\reaction\ReactionHandler;

/**
 * Trait for dbo collections with reactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionReactions
{
    /**
     * @var array<int, ReactionData>
     */
    private array $reactionData;

    public function getReactionData(DatabaseObject $object): ReactionData
    {
        $this->loadReactionData();

        return $this->reactionData[$object->getObjectID()];
    }

    public function getCachedReactions(DatabaseObject $object): ?string
    {
        return $this->getReactionData($object)->cachedReactions;
    }

    private function loadReactionData(): void
    {
        if (isset($this->reactionData)) {
            return;
        }

        $this->reactionData = [];

        $objectType = ReactionHandler::getInstance()->getObjectType($this->getReactionObjectType());
        ReactionHandler::getInstance()->loadLikeObjects($objectType, $this->getObjectIDs());

        foreach ($this->getObjectIDs() as $objectID) {
            $likeObject = ReactionHandler::getInstance()->getLikeObject($objectType, $objectID);
            if ($likeObject !== null) {
                $this->reactionData[$objectID] = new ReactionData(
                    $this->getReactionObjectType(),
                    $objectID,
                    $likeObject->reactionTypeID ?: 0,
                    $likeObject->cachedReactions,
                    $likeObject->getReactionsJson()
                );
            } else {
                $this->reactionData[$objectID] = new ReactionData(
                    $this->getReactionObjectType(),
                    $objectID,
                );
            }
        }
    }

    protected abstract function getReactionObjectType(): string;
}
