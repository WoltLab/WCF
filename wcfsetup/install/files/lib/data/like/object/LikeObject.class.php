<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\system\WCF;

/**
 * Represents a liked object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $likeObjectID       unique id of the liked object
 * @property-read   int     $objectTypeID       id of the `com.woltlab.wcf.like.likeableObject` object type
 * @property-read   int     $objectID           id of the liked object
 * @property-read   ?int    $objectUserID       id of the user who created the liked object or null if user has been deleted or object was created by guest
 * @property-read   int     $likes              number of likes of the liked object
 * @property-read   int     $cumulativeLikes    number of likes of the liked object (copy of $likes)
 * @property-read   ?string $cachedReactions    JSON array with the reactionTypeIDs and the count of the reactions
 * @property-read   int     $reactionTypeID
 * @phpstan-type ReactionData array{
 *  reactionCount: int,
 *  renderedReactionIcon: string,
 *  renderedReactionIconEncoded: string,
 *  reactionTitle: string,
 * }
 */
class LikeObject extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'likeObjectID';

    protected ?ILikeObject $likedObject = null;

    /**
     * An array with all reaction types, which were received for the object. As key, the reactionTypeID
     * is used. As value there is another array. If the object does not received any reaction yet,
     * an empty array is returned.
     * @var array<int, ReactionData>
     */
    protected array $reactions = [];

    #[\Override]
    protected function handleData(array $data)
    {
        parent::handleData($data);

        // get user objects from cache
        if (!empty($data['cachedReactions'])) {
            $cachedReactions = \json_decode($data['cachedReactions'], true, flags: \JSON_THROW_ON_ERROR);

            if (\is_array($cachedReactions)) {
                foreach ($cachedReactions as $reactionTypeID => $reactionCount) {
                    $reactionType = ReactionTypeCache::getInstance()->getReactionTypeByID($reactionTypeID);

                    // prevent outdated reactions
                    if ($reactionType !== null) {
                        $this->reactions[$reactionTypeID] = [
                            'reactionCount' => $reactionCount,
                            'renderedReactionIcon' => $reactionType->renderIcon(),
                            'renderedReactionIconEncoded' => \json_encode($reactionType->renderIcon(), \JSON_THROW_ON_ERROR),
                            'reactionTitle' => $reactionType->getTitle(),
                        ];
                    }
                }
            }
        }

        // Old property that is set for backward compatibility reasons.
        $this->data['dislikes'] = 0;
    }

    public function getLikedObject(): ?ILikeObject
    {
        if ($this->likedObject === null) {
            $this->likedObject = ObjectTypeCache::getInstance()
                ->getObjectType($this->objectTypeID)
                ->getProcessor()
                ->getObjectByID($this->objectID);
        }

        return $this->likedObject;
    }

    /**
     * Returns an array with all reaction types, which were received for the object.
     *
     * @return array<int, ReactionData>
     * @since 5.2
     */
    public function getReactions(): array
    {
        return $this->reactions;
    }

    /**
     * @since 6.0
     */
    public function getReactionsJson(): string
    {
        $data = [];
        foreach ($this->reactions as $reactionTypeID => $value) {
            $data[] = [
                $reactionTypeID,
                $value['reactionCount'],
            ];
        }

        return \json_encode($data, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<int, int>
     * @since 6.3
     */
    public function getCachedReactions(): array
    {
        $data = [];
        foreach ($this->reactions as $reactionTypeID => $value) {
            $data[$reactionTypeID] = $value['reactionCount'];
        }

        return $data;
    }

    /**
     * Returns the like object with the given type and object id.
     */
    public static function getLikeObject(int $objectTypeID, int $objectID): LikeObject
    {
        $sql = "SELECT  *
                FROM    wcf1_like_object
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
        ]);
        $row = $statement->fetchArray();

        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }
}
