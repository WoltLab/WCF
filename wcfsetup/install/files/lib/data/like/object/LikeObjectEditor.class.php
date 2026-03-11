<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObjectEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Extends the LikeObject object with functions to create, update and delete liked objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin LikeObject
 * @extends DatabaseObjectEditor<LikeObject>
 */
class LikeObjectEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = LikeObject::class;

    /**
     * Recalculates the values for the columns `likes`, `cumulativeLikes` and `cachedReactions` for the given rows.
     *
     * @param list<int> $likeObjectIDs
     * @since 6.3
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
     *
     * @since 6.3
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
     *
     * @since 6.3
     */
    public static function getLikeObjectForUpdate(ILikeObject $likeable): LikeObject
    {
        $sql = "SELECT * FROM wcf1_like_object WHERE objectTypeID = ? AND objectID = ? FOR UPDATE";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$likeable->getObjectType()->objectTypeID, $likeable->getObjectID()]);

        return $statement->fetchSingleObject(LikeObject::class);
    }
}
