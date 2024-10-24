<?php

namespace wcf\system\like;

use wcf\data\like\Like;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\system\reaction\ReactionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the likes of liked objects.
 *
 * Usage (retrieve all likes for a list of objects):
 * // get type object
 * $objectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.foo.bar');
 * // load like data
 * LikeHandler::getInstance()->loadLikeObjects($objectType, $objectIDs);
 * // get like data
 * $likeObjects = LikeHandler::getInstance()->getLikeObjects($objectType);
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  The LikeHandler is deprecated since 5.2 in favor of the \wcf\system\reaction\ReactionHandler
 */
class LikeHandler extends SingletonFactory
{
    /**
     * loaded like objects
     * @var LikeObject[][]
     */
    protected $likeObjectCache = [];

    /**
     * cached object types
     * @var ObjectType[]
     */
    protected $cache;

    /**
     * Creates a new LikeHandler instance.
     */
    protected function init()
    {
        // does nothing
    }

    /**
     * Returns an object type from cache.
     *
     * @param string $objectName
     * @return  ObjectType
     */
    public function getObjectType($objectName)
    {
        return ReactionHandler::getInstance()->getObjectType($objectName);
    }

    /**
     * Returns a like object.
     *
     * @param ObjectType $objectType
     * @param int $objectID
     * @return  LikeObject|null
     */
    public function getLikeObject(ObjectType $objectType, $objectID)
    {
        return ReactionHandler::getInstance()->getLikeObject($objectType, $objectID);
    }

    /**
     * Returns the like objects of a specific object type.
     *
     * @param ObjectType $objectType
     * @return  LikeObject[]
     */
    public function getLikeObjects(ObjectType $objectType)
    {
        return ReactionHandler::getInstance()->getLikeObjects($objectType);
    }

    /**
     * Loads the like data for a set of objects and returns the number of loaded
     * like objects
     *
     * @param ObjectType $objectType
     * @param array $objectIDs
     * @return  int
     */
    public function loadLikeObjects(ObjectType $objectType, array $objectIDs)
    {
        return ReactionHandler::getInstance()->loadLikeObjects($objectType, $objectIDs);
    }

    /**
     * Saves the like of an object.
     *
     * @param ILikeObject $likeable
     * @param User $user
     * @param int $likeValue
     * @param int $time
     * @return  array
     */
    public function like(ILikeObject $likeable, User $user, $likeValue, $time = TIME_NOW)
    {
        $reactionTypeID = null;
        if ($likeValue == 1) {
            $reactionTypeID = ReactionHandler::getInstance()->getFirstReactionTypeID();
        }

        if ($reactionTypeID === null) {
            return [
                'data' => [],
                'like' => 0,
                'newValue' => 0,
                'oldValue' => 0,
                'users' => [],
            ];
        }

        $reactData = ReactionHandler::getInstance()->react($likeable, $user, $reactionTypeID, $time);

        return [
            'data' => $this->loadLikeStatus($reactData['likeObject'], $user),
            'like' => $reactData['like'],
            'newValue' => 0,
            'oldValue' => 0,
            'users' => [],
        ];
    }

    /**
     * Reverts the like of an object.
     *
     * @param Like $like
     * @param ILikeObject $likeable
     * @param LikeObject $likeObject
     * @param User $user
     * @return  array
     */
    public function revertLike(Like $like, ILikeObject $likeable, LikeObject $likeObject, User $user)
    {
        $reactData = ReactionHandler::getInstance()->revertReact($like, $likeable, $likeObject, $user);

        return [
            'data' => $this->loadLikeStatus($reactData['likeObject'], $user),
            'like' => null,
            'newValue' => 0,
            'oldValue' => 0,
            'users' => [],
        ];
    }

    /**
     * Removes all likes for given objects.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     * @param string[] $notificationObjectTypes
     */
    public function removeLikes($objectType, array $objectIDs, array $notificationObjectTypes = [])
    {
        ReactionHandler::getInstance()->removeReactions($objectType, $objectIDs, $notificationObjectTypes);
    }

    /**
     * Returns current like object status.
     *
     * @param LikeObject $likeObject
     * @param User $user
     * @return  array
     */
    protected function loadLikeStatus(LikeObject $likeObject, User $user)
    {
        $sql = "SELECT      like_object.likes, like_object.dislikes, like_object.cumulativeLikes,
                            CASE WHEN like_table.likeValue IS NOT NULL THEN like_table.likeValue ELSE 0 END AS liked
                FROM        wcf1_like_object like_object
                LEFT JOIN   wcf1_like like_table
                ON          like_table.objectTypeID = ?
                        AND like_table.objectID = like_object.objectID
                        AND like_table.userID = ?
                WHERE       like_object.likeObjectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $likeObject->objectTypeID,
            $user->userID,
            $likeObject->likeObjectID,
        ]);

        $row = $statement->fetchSingleRow();
        if ($row === false) {
            $row = [
                'likes' => 0,
                'dislikes' => 0,
                'cumulativeLikes' => 0,
                'liked' => 0,
            ];
        }

        return $row;
    }
}
