<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\user\User;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Represents a liked object.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Like\Object
 *
 * @property-read   int $likeObjectID       unique id of the liked object
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.like.likeableObject` object type
 * @property-read   int $objectID       id of the liked object
 * @property-read   int|null $objectUserID       id of the user who created the liked object or null if user has been deleted or object was created by guest
 * @property-read   int $likes          number of likes of the liked object
 * @property-read   int $dislikes       legacy column, not used anymore
 * @property-read   int $cumulativeLikes    number of likes of the liked object
 * @property-read   string $cachedUsers        serialized array with the ids and names of the three users who liked (+1) the object last
 * @property-read   string $cachedReactions    serialized array with the reactionTypeIDs and the count of the reactions
 */
class LikeObject extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'likeObjectID';

    /**
     * liked object
     * @var ILikeObject
     */
    protected $likedObject;

    /**
     * list of users who liked this object
     * @var User[]
     */
    protected $users = [];

    /**
     * A list with all reaction counts.
     * @var int[]
     */
    protected $reactions = [];

    /**
     * The reputation for the current object.
     * @var int
     */
    protected $reputation;

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        // get user objects from cache
        if (!empty($data['cachedUsers'])) {
            $cachedUsers = @\unserialize($data['cachedUsers']);

            if (\is_array($cachedUsers)) {
                foreach ($cachedUsers as $cachedUserData) {
                    $user = new User(null, $cachedUserData);
                    $this->users[$user->userID] = $user;
                }
            }
        }

        // get user objects from cache
        if (!empty($data['cachedReactions'])) {
            $cachedReactions = @\unserialize($data['cachedReactions']);

            if (\is_array($cachedReactions)) {
                foreach ($cachedReactions as $reactionTypeID => $reactionCount) {
                    $reactionType = ReactionTypeCache::getInstance()->getReactionTypeByID($reactionTypeID);

                    // prevent outdated reactions
                    if ($reactionType !== null) {
                        $this->reactions[$reactionTypeID] = [
                            'reactionCount' => $reactionCount,
                            'renderedReactionIcon' => $reactionType->renderIcon(),
                            'renderedReactionIconEncoded' => JSON::encode($reactionType->renderIcon()),
                            'reactionTitle' => $reactionType->getTitle(),
                            'reactionType' => $reactionType->type,
                        ];
                    }
                }
            }
        }
    }

    /**
     * Since version 5.2, this method returns all reactionCounts for the different reactionTypes,
     * instead of the user (as the method name suggests). This behavior is intentional and helps
     * to establish backward compatibility.
     *
     * @return  mixed[]
     * @deprecated  since 5.2
     */
    public function getUsers()
    {
        $returnValues = [];

        foreach ($this->getReactions() as $reactionID => $reaction) {
            $returnValues[] = (object)[
                'userID' => $reactionID,
                'username' => $reaction['reactionCount'],
            ];
        }

        // this value is only set, if the object was loaded over the ReactionHandler::loadLikeObjects()
        if ($this->reactionTypeID) {
            $returnValues[] = (object)[
                'userID' => 'reactionTypeID',
                'username' => $this->reactionTypeID,
            ];
        }

        return $returnValues;
    }

    /**
     * Returns the liked object.
     *
     * @return  ILikeObject
     */
    public function getLikedObject()
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
     * Returns all reaction counts for this object. Reactions without any count won't be saved in the array.
     * So this method returns an empty array, if this object has no reactions.
     *
     * @return      int[]
     * @since   5.2
     */
    public function getReactions()
    {
        return $this->reactions;
    }

    /**
     * Sets the liked object.
     *
     * @param ILikeObject $likedObject
     */
    public function setLikedObject(ILikeObject $likedObject)
    {
        $this->likedObject = $likedObject;
    }

    /**
     * Returns the like object with the given type and object id.
     *
     * @param int $objectTypeID
     * @param int $objectID
     * @return  LikeObject
     */
    public static function getLikeObject($objectTypeID, $objectID)
    {
        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_like_object
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
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
