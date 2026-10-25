<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObjectBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting liked objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<LikeObject>
 */
final class LikeObjectBuilder extends DatabaseObjectBuilder
{
    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    public function setObjectUserID(?int $objectUserID): static
    {
        $this->properties['objectUserID'] = $objectUserID;

        return $this;
    }

    /**
     * Sets the number of likes, `cumulativeLikes` is kept as a copy of this value.
     */
    public function setLikes(int $likes): static
    {
        $this->properties['likes'] = $likes;
        $this->properties['cumulativeLikes'] = $likes;

        return $this;
    }

    /**
     * Sets the number of reactions per reaction type.
     *
     * @param array<int, int> $cachedReactions reactionTypeID => count
     */
    public function setCachedReactions(array $cachedReactions): static
    {
        $this->properties['cachedReactions'] = $cachedReactions === []
            ? null
            : \json_encode($cachedReactions, \JSON_FORCE_OBJECT | \JSON_THROW_ON_ERROR);

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'objectID'];
    }

    /**
     * Recalculates the values for the columns `likes`, `cumulativeLikes` and `cachedReactions` for the given rows.
     *
     * @param list<int> $likeObjectIDs
     */
    public static function rebuildLikeObjectData(array $likeObjectIDs): void
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('likeObjectID IN (?)', [$likeObjectIDs]);

        $sql = "UPDATE  wcf1_like_object like_object
                SET     likes = (
                            SELECT  COUNT(*)
                            FROM    wcf1_like
                            WHERE   objectTypeID = like_object.objectTypeID
                                AND objectID = like_object.objectID
                        ),
                        cumulativeLikes = likes,
                        cachedReactions = (
                            SELECT  JSON_OBJECTAGG(reactionTypeID, count)
                            FROM    (SELECT reactionTypeID, COUNT(*) AS count FROM wcf1_like WHERE objectTypeID = like_object.objectTypeID AND objectID = like_object.objectID GROUP BY reactionTypeID) AS cachedReactions
                        )
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    /**
     * Creates a row for the given likeable object.
     */
    public static function createFromLikeable(ILikeObject $likeable): void
    {
        $sql = "INSERT IGNORE INTO wcf1_like_object (objectTypeID, objectID, objectUserID) VALUES (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $likeable->getObjectType()->objectTypeID,
            $likeable->getObjectID(),
            $likeable->getUserID(),
        ]);
    }

    /**
     * Returns the `LikeObject` for the given likeable object and locks the row for an update.
     */
    public static function getLikeObjectForUpdate(ILikeObject $likeable): LikeObject
    {
        $sql = "SELECT * FROM wcf1_like_object WHERE objectTypeID = ? AND objectID = ? FOR UPDATE";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$likeable->getObjectType()->objectTypeID, $likeable->getObjectID()]);

        return $statement->fetchSingleObject(LikeObject::class);
    }
}
