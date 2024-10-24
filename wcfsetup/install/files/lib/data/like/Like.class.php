<?php

namespace wcf\data\like;

use wcf\data\DatabaseObject;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a like of an object.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $likeID         unique id of the like
 * @property-read   int $objectID       id of the liked object
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.like.likeableObject` object type
 * @property-read   int|null $objectUserID       id of the user who created the liked object or null if user has been deleted or object was created by guest
 * @property-read   int $userID         id of the user who created the like
 * @property-read   int $time           timestamp at which the like has been created
 * @property-read   int $likeValue      value of the like (`+1` = like, `-1` = dislike, see `Like::LIKE` and `Like::Dislike`)
 * @property-read   int $reactionTypeID     reactionTypeID of the reaction
 */
class Like extends DatabaseObject
{
    /**
     * like value
     * @var int
     */
    const LIKE = 1;

    /**
     * dislike value
     * @var int
     */
    const DISLIKE = -1;

    /**
     * Returns the title of the associated reaction type.
     *
     * @since       5.3
     */
    public function __toString(): string
    {
        return $this->getReactionType()->getTitle();
    }

    /**
     * Renders the like by showing the associated reaction type's icon.
     *
     * @return      string
     * @since       5.3
     */
    public function render()
    {
        return '<span title="' . StringUtil::encodeHTML($this) . '" class="jsTooltip">' . $this->getReactionType()->renderIcon() . '</span>';
    }

    /**
     * Returns the like with given type, object id and user id.
     *
     * @param int $objectTypeID
     * @param int $objectID
     * @param int $userID
     * @return  Like
     */
    public static function getLike($objectTypeID, $objectID, $userID)
    {
        $sql = "SELECT  *
                FROM    wcf1_like
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
            $userID,
        ]);

        $row = $statement->fetchArray();

        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableAlias()
    {
        return 'like_table';
    }

    /**
     * Returns true, if like value is a like.
     *
     * @return  bool
     * @deprecated  5.2
     */
    public function isLike()
    {
        return true;
    }

    /**
     * Returns true, if like value is a dislike.
     *
     * @return  bool
     * @deprecated  5.2
     */
    public function isDislike()
    {
        return false;
    }

    /**
     * Returns the reaction for these like.
     *
     * @return  ReactionType
     * @since   5.2
     */
    public function getReactionType()
    {
        return ReactionTypeCache::getInstance()->getReactionTypeByID($this->reactionTypeID);
    }
}
