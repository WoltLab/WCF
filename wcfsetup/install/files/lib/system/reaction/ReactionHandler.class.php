<?php

namespace wcf\system\reaction;

use wcf\data\DatabaseObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\Like;
use wcf\data\like\LikeList;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\like\object\LikeObjectAction;
use wcf\data\like\object\LikeObjectList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\object\IReactionObject;
use wcf\data\reaction\ReactionAction;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles the reactions of objects.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ReactionHandler extends SingletonFactory
{
    /**
     * @var LikeObject[][]
     */
    private array $likeObjectCache = [];

    /**
     * @var ObjectType[]
     */
    private array $cache;

    /**
     * @var ILikeObject[][]
     */
    private array $likeableObjectsCache = [];

    protected function init(): void
    {
        $this->cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.like.likeableObject');
    }

    /**
     * Returns the JSON encoded JavaScript variable for the template.
     */
    public function getReactionsJSVariable(): string
    {
        $reactions = ReactionTypeCache::getInstance()->getReactionTypes();

        $returnValues = [];

        foreach ($reactions as $reaction) {
            $returnValues[$reaction->reactionTypeID] = [
                'title' => $reaction->getTitle(),
                'renderedIcon' => $reaction->renderIcon(),
                'iconPath' => $reaction->getIconPath(),
                'showOrder' => $reaction->showOrder,
                'reactionTypeID' => $reaction->reactionTypeID,
                'isAssignable' => $reaction->isAssignable,
            ];
        }

        return \json_encode($returnValues, \JSON_THROW_ON_ERROR);
    }

    /**
     * Returns all enabled reaction types.
     *
     * @return ReactionType[]
     */
    public function getReactionTypes(): array
    {
        return ReactionTypeCache::getInstance()->getReactionTypes();
    }

    public function getReactionTypeByID(int $reactionID): ?ReactionType
    {
        return ReactionTypeCache::getInstance()->getReactionTypeByID($reactionID);
    }

    /**
     * Builds the data attributes for the object container.
     */
    public function getDataAttributes(string $objectTypeName, int $objectID): string
    {
        $object = $this->getLikeableObject($objectTypeName, $objectID);

        $dataAttributes = [
            'object-id' => $object->getObjectID(),
            'object-type' => $objectTypeName,
            'user-id' => $object->getUserID(),
        ];

        EventHandler::getInstance()->fireAction($this, 'getDataAttributes', $dataAttributes);

        $returnDataAttributes = '';

        foreach ($dataAttributes as $key => $value) {
            if (!\preg_match('/^[a-z0-9-]+$/', $key)) {
                throw new \RuntimeException("Invalid key '" . $key . "' for data attribute.");
            }

            if (!empty($returnDataAttributes)) {
                $returnDataAttributes .= ' ';
            }

            $returnDataAttributes .= 'data-' . $key . '="' . StringUtil::encodeHTML($value) . '"';
        }

        return $returnDataAttributes;
    }

    /**
     * @param list<int> $objectIDs
     */
    public function cacheLikeableObjects(string $objectTypeName, array $objectIDs): void
    {
        $objectType = $this->getObjectType($objectTypeName);
        if ($objectType === null) {
            throw new \InvalidArgumentException(
                "ObjectName '{$objectTypeName}' is unknown for definition 'com.woltlab.wcf.like.likeableObject'."
            );
        }

        /** @var ILikeObjectTypeProvider<DatabaseObject> $objectTypeProcessor */
        $objectTypeProcessor = $objectType->getProcessor();

        $objects = $objectTypeProcessor->getObjectsByIDs($objectIDs);

        if (!isset($this->likeableObjectsCache[$objectTypeName])) {
            $this->likeableObjectsCache[$objectTypeName] = [];
        }

        foreach ($objects as $object) {
            $this->likeableObjectsCache[$objectTypeName][$object->getObjectID()] = $object;
        }
    }

    /**
     * Get an likeable object from the internal cache.
     */
    public function getLikeableObject(string $objectTypeName, int $objectID): ILikeObject
    {
        if (!isset($this->likeableObjectsCache[$objectTypeName][$objectID])) {
            $this->cacheLikeableObjects($objectTypeName, [$objectID]);
        }

        $likeableObject = $this->likeableObjectsCache[$objectTypeName][$objectID] ?? null;
        if ($likeableObject === null) {
            throw new \InvalidArgumentException(
                "Object with the object id '{$objectID}' for object type '{$objectTypeName}' is unknown."
            );
        }

        // @phpstan-ignore instanceof.alwaysTrue
        if (!($likeableObject instanceof ILikeObject)) {
            throw new ImplementationException(
                // @phpstan-ignore argument.type
                \get_class($likeableObject),
                ILikeObject::class
            );
        }

        return $likeableObject;
    }

    public function getObjectType(string $objectName): ?ObjectType
    {
        return $this->cache[$objectName] ?? null;
    }

    public function getLikeObject(ObjectType $objectType, int $objectID): ?LikeObject
    {
        if (!isset($this->likeObjectCache[$objectType->objectTypeID][$objectID])) {
            $this->loadLikeObjects($objectType, [$objectID]);
        }

        return $this->likeObjectCache[$objectType->objectTypeID][$objectID] ?? null;
    }

    /**
     * Returns the like objects of a specific object type.
     *
     * @return LikeObject[]
     */
    public function getLikeObjects(ObjectType $objectType): array
    {
        if (isset($this->likeObjectCache[$objectType->objectTypeID])) {
            return $this->likeObjectCache[$objectType->objectTypeID];
        }

        return [];
    }

    /**
     * Loads the like data for a set of objects and returns the number of loaded
     * like objects.
     *
     * @param list<int> $objectIDs
     */
    public function loadLikeObjects(ObjectType $objectType, array $objectIDs): int
    {
        if (empty($objectIDs)) {
            return 0;
        }

        $this->cacheLikeableObjects($objectType->objectType, $objectIDs);

        $i = 0;

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("like_object.objectTypeID = ?", [$objectType->objectTypeID]);
        $conditions->add("like_object.objectID IN (?)", [$objectIDs]);
        $parameters = $conditions->getParameters();

        if (WCF::getUser()->userID) {
            $sql = "SELECT      like_object.*,
                                COALESCE(like_table.reactionTypeID, 0) AS reactionTypeID,
                                COALESCE(like_table.likeValue, 0) AS liked
                    FROM        wcf1_like_object like_object
                    LEFT JOIN   wcf1_like like_table
                    ON          like_table.objectTypeID = like_object.objectTypeID
                            AND like_table.objectID = like_object.objectID
                            AND like_table.userID = ?
                    " . $conditions;

            \array_unshift($parameters, WCF::getUser()->userID);
        } else {
            $sql = "SELECT  like_object.*, 0 AS liked
                    FROM    wcf1_like_object like_object
                    " . $conditions;
        }

        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($parameters);
        while ($row = $statement->fetchArray()) {
            $this->likeObjectCache[$objectType->objectTypeID][$row['objectID']] = new LikeObject(null, $row);
            $i++;
        }

        return $i;
    }

    /**
     * Add a reaction to an object.
     *
     * @return array{
     *  cachedReactions: array<int, int>,
     *  reactionTypeID: ?int,
     *  like?: Like,
     *  likeObject: LikeObject|array{},
     *  cumulativeLikes: int,
     * }
     */
    public function react(ILikeObject $likeable, User $user, int $reactionTypeID, int $time = \TIME_NOW): array
    {
        // verify if object is already liked by user
        $like = Like::getLike($likeable->getObjectType()->objectTypeID, $likeable->getObjectID(), $user->userID);

        // get like object
        $likeObject = LikeObject::getLikeObject($likeable->getObjectType()->objectTypeID, $likeable->getObjectID());

        // if vote is identically just revert the vote
        if ($like->likeID && ($like->reactionTypeID == $reactionTypeID)) {
            return $this->revertReact($like, $likeable, $likeObject, $user);
        }

        $reaction = ReactionTypeCache::getInstance()->getReactionTypeByID($reactionTypeID);

        try {
            WCF::getDB()->beginTransaction();

            $likeObjectData = $this->updateLikeObject($likeable, $likeObject, $like, $reaction);

            // update owner's like counter
            $this->updateUsersLikeCounter($likeable, $like, $reaction);

            if (!$like->likeID) {
                // save like
                $sql = "INSERT INTO             wcf1_like
                                                (objectID, objectTypeID, objectUserID, userID, time, likeValue, reactionTypeID)
                        VALUES                  (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE time = ?,
                                                likeValue = ?,
                                                reactionTypeID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $likeable->getObjectID(),
                    $likeable->getObjectType()->objectTypeID,
                    $likeable->getUserID() ?: null,
                    $user->userID,
                    $time,
                    1,
                    $reactionTypeID,
                    $time,
                    1,
                    $reactionTypeID,
                ]);
                $like = new Like(WCF::getDB()->getInsertID("wcf1_like", "likeID"));

                if ($likeable->getUserID()) {
                    UserActivityPointHandler::getInstance()->fireEvent(
                        'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
                        $like->likeID,
                        $likeable->getUserID()
                    );
                }
            } else {
                (new ReactionAction([$like], 'update', [
                    'data' => [
                        'time' => $time,
                        'likeValue' => 1,
                        'reactionTypeID' => $reactionTypeID,
                    ],
                ]))->executeAction();

                if ($like->reactionTypeID == $reactionTypeID) {
                    if ($likeable->getUserID()) {
                        UserActivityPointHandler::getInstance()->removeEvents(
                            'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
                            [$likeable->getUserID() => 1]
                        );
                    }
                }
            }

            // update object's like counter
            $likeable->updateLikeCounter($likeObjectData['cumulativeLikes']);

            // update recent activity
            if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType . '.recentActivityEvent')) {
                $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
                    'com.woltlab.wcf.user.recentActivityEvent',
                    $likeable->getObjectType()->objectType . '.recentActivityEvent'
                );

                if ($objectType->supportsReactions) {
                    if ($like->likeID) {
                        UserActivityEventHandler::getInstance()->removeEvent(
                            $likeable->getObjectType()->objectType . '.recentActivityEvent',
                            $likeable->getObjectID(),
                            $user->userID
                        );
                    }

                    UserActivityEventHandler::getInstance()->fireEvent(
                        $likeable->getObjectType()->objectType . '.recentActivityEvent',
                        $likeable->getObjectID(),
                        $likeable->getLanguageID(),
                        $user->userID,
                        TIME_NOW,
                        [
                            'reactionTypeID' => $reaction->reactionTypeID,
                            /* @deprecated 6.1 use `reactionTypeID` */
                            'reactionType' => $reaction,
                        ]
                    );
                }
            }

            WCF::getDB()->commitTransaction();

            // This interface should help to determine whether the plugin has been adapted to the API 5.2.
            // If a LikeableObject does not implement this interface, no notification will be sent, because
            // we assume, that the plugin has not been adapted to the new API.
            if ($likeable instanceof IReactionObject) {
                $likeable->sendNotification($like);
            }

            return [
                'cachedReactions' => $likeObjectData['cachedReactions'],
                'reactionTypeID' => $reactionTypeID,
                'like' => $like,
                'likeObject' => $likeObjectData['likeObject'],
                'cumulativeLikes' => $likeObjectData['cumulativeLikes'],
            ];
        } catch (DatabaseQueryException $e) {
            WCF::getDB()->rollBackTransaction();

            throw $e;
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \LogicException('Unreachable');
    }

    /**
     * Creates or updates a LikeObject for an likeable object.
     *
     * @return array{cumulativeLikes: int, cachedReactions: array<int, int>, likeObject: LikeObject}
     */
    private function updateLikeObject(
        ILikeObject $likeable,
        LikeObject $likeObject,
        Like $like,
        ReactionType $reactionType
    ): array {
        // update existing object
        if ($likeObject->likeObjectID) {
            $cumulativeLikes = $likeObject->cumulativeLikes;

            if ($likeObject->cachedReactions !== null) {
                $cachedReactions = @\unserialize($likeObject->cachedReactions);
            } else {
                $cachedReactions = [];
            }

            if (!\is_array($cachedReactions)) {
                $cachedReactions = [];
            }

            if ($like->likeID) {
                $cumulativeLikes--;

                if (isset($cachedReactions[$like->getReactionType()->reactionTypeID])) {
                    if (--$cachedReactions[$like->getReactionType()->reactionTypeID] == 0) {
                        unset($cachedReactions[$like->getReactionType()->reactionTypeID]);
                    }
                }
            }

            $cumulativeLikes++;

            if (isset($cachedReactions[$reactionType->reactionTypeID])) {
                $cachedReactions[$reactionType->reactionTypeID]++;
            } else {
                $cachedReactions[$reactionType->reactionTypeID] = 1;
            }

            $cachedReactions = self::cleanUpCachedReactions($cachedReactions);

            // build update date
            $updateData = [
                'likes' => $cumulativeLikes,
                'dislikes' => 0,
                'cumulativeLikes' => $cumulativeLikes,
                'cachedReactions' => \serialize($cachedReactions),
            ];

            // update data
            (new LikeObjectAction([$likeObject], 'update', ['data' => $updateData]))->executeAction();
        } else {
            $cumulativeLikes = 1;
            $cachedReactions = [
                $reactionType->reactionTypeID => 1,
            ];

            // create cache
            $sql = "INSERT INTO             wcf1_like_object
                                            (objectTypeID, objectID, objectUserID, likes, dislikes, cumulativeLikes, cachedReactions)
                    VALUES                  (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE likes = ?,
                                            dislikes = ?,
                                            cumulativeLikes = ?,
                                            cachedReactions = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $likeable->getObjectType()->objectTypeID,
                $likeable->getObjectID(),
                $likeable->getUserID() ?: null,
                $cumulativeLikes,
                0,
                $cumulativeLikes,
                \serialize($cachedReactions),
                $cumulativeLikes,
                0,
                $cumulativeLikes,
                \serialize($cachedReactions),
            ]);
            $likeObject = new LikeObject(WCF::getDB()->getInsertID("wcf1_like_object", "likeObjectID"));
        }

        return [
            'cumulativeLikes' => $cumulativeLikes,
            'cachedReactions' => $cachedReactions,
            'likeObject' => $likeObject,
        ];
    }

    /**
     * Updates the like counter for a user.
     */
    private function updateUsersLikeCounter(
        ILikeObject $likeable,
        Like $like,
        ?ReactionType $reactionType = null
    ): void {
        if ($likeable->getUserID()) {
            $likesReceived = 0;
            if ($like->likeID) {
                $likesReceived--;
            }

            if ($reactionType !== null) {
                $likesReceived++;
            }

            if ($likesReceived !== 0) {
                $userEditor = new UserEditor(UserRuntimeCache::getInstance()->getObject($likeable->getUserID()));
                $userEditor->updateCounters(['likesReceived' => $likesReceived]);
            }
        }
    }

    /**
     * Reverts a reaction for an object.
     *
     * @return array{
     *  cachedReactions: array<int, int>,
     *  reactionTypeID: null,
     *  likeObject: LikeObject|array{},
     *  cumulativeLikes: ?int,
     * }
     */
    public function revertReact(Like $like, ILikeObject $likeable, LikeObject $likeObject, User $user): array
    {
        if (!$like->likeID) {
            throw new \InvalidArgumentException('The given parameter $like is invalid.');
        }

        try {
            WCF::getDB()->beginTransaction();

            $likeObjectData = $this->revertLikeObject($likeObject, $like);

            // update owner's like counter
            $this->updateUsersLikeCounter($likeable, $like, null);

            (new ReactionAction([$like], 'delete'))->executeAction();

            if ($likeable->getUserID()) {
                UserActivityPointHandler::getInstance()->removeEvents(
                    'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
                    [$likeable->getUserID() => 1]
                );
            }

            // update object's like counter
            $likeable->updateLikeCounter($likeObjectData['cumulativeLikes']);

            // delete recent activity
            if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType . '.recentActivityEvent')) {
                UserActivityEventHandler::getInstance()->removeEvent(
                    $likeable->getObjectType()->objectType . '.recentActivityEvent',
                    $likeable->getObjectID(),
                    $user->userID
                );
            }

            WCF::getDB()->commitTransaction();

            return [
                'cachedReactions' => $likeObjectData['cachedReactions'],
                'reactionTypeID' => null,
                'likeObject' => $likeObjectData['likeObject'],
                'cumulativeLikes' => $likeObjectData['cumulativeLikes'],
            ];
        } catch (DatabaseQueryException $e) {
            WCF::getDB()->rollBackTransaction();
        }

        return [
            'cachedReactions' => [],
            'reactionTypeID' => null,
            'likeObject' => [],
            'cumulativeLikes' => null,
        ];
    }

    /**
     * Creates or updates a LikeObject for an likable object.
     *
     * @return array{cumulativeLikes: int, cachedReactions: array<int, int>, likeObject: LikeObject}
     */
    private function revertLikeObject(LikeObject $likeObject, Like $like): array
    {
        if (!$likeObject->likeObjectID) {
            throw new \InvalidArgumentException('The given parameter $likeObject is invalid.');
        }

        // update existing object
        $cumulativeLikes = $likeObject->cumulativeLikes;
        $cachedReactions = @\unserialize($likeObject->cachedReactions);
        if (!\is_array($cachedReactions)) {
            $cachedReactions = [];
        }

        if ($like->likeID) {
            $cumulativeLikes--;

            if (isset($cachedReactions[$like->getReactionType()->reactionTypeID])) {
                if (--$cachedReactions[$like->getReactionType()->reactionTypeID] == 0) {
                    unset($cachedReactions[$like->getReactionType()->reactionTypeID]);
                }
            }

            $cachedReactions = self::cleanUpCachedReactions($cachedReactions);

            // build update date
            $updateData = [
                'likes' => $cumulativeLikes,
                'dislikes' => 0,
                'cumulativeLikes' => $cumulativeLikes,
                'cachedReactions' => \serialize($cachedReactions),
            ];

            // update data
            (new LikeObjectAction([$likeObject], 'update', ['data' => $updateData]))->executeAction();
        }

        return [
            'cumulativeLikes' => $cumulativeLikes,
            'cachedReactions' => $cachedReactions,
            'likeObject' => $likeObject,
        ];
    }

    /**
     * Removes all reactions for given objects.
     *
     * @param int[] $objectIDs
     * @param string[] $notificationObjectTypes
     */
    public function removeReactions($objectType, array $objectIDs, array $notificationObjectTypes = []): void
    {
        $objectTypeObj = $this->getObjectType($objectType);

        if ($objectTypeObj === null) {
            throw new \InvalidArgumentException('Given objectType is invalid.');
        }

        // get like objects
        $likeObjectList = new LikeObjectList();
        $likeObjectList->getConditionBuilder()->add('like_object.objectTypeID = ?', [$objectTypeObj->objectTypeID]);
        $likeObjectList->getConditionBuilder()->add('like_object.objectID IN (?)', [$objectIDs]);
        $likeObjectList->readObjects();
        $likeObjects = $likeObjectList->getObjects();
        $likeObjectIDs = $likeObjectList->getObjectIDs();

        // reduce count of received users
        $users = [];
        foreach ($likeObjects as $likeObject) {
            if ($likeObject->likes && $likeObject->objectUserID) {
                if (!isset($users[$likeObject->objectUserID])) {
                    $users[$likeObject->objectUserID] = 0;
                }

                $users[$likeObject->objectUserID] -= \count($likeObject->getReactions());
            }
        }

        foreach ($users as $userID => $reactionData) {
            $userEditor = new UserEditor(new User(null, ['userID' => $userID]));
            $userEditor->updateCounters([
                'likesReceived' => $reactionData,
            ]);
        }

        // get like ids
        $likeList = new LikeList();
        $likeList->getConditionBuilder()->add('like_table.objectTypeID = ?', [$objectTypeObj->objectTypeID]);
        $likeList->getConditionBuilder()->add('like_table.objectID IN (?)', [$objectIDs]);
        $likeList->readObjects();

        if (\count($likeList)) {
            $activityPoints = $likeData = [];
            foreach ($likeList as $like) {
                $likeData[$like->likeID] = $like->userID;

                if ($like->objectUserID) {
                    if (!isset($activityPoints[$like->objectUserID])) {
                        $activityPoints[$like->objectUserID] = 0;
                    }
                    $activityPoints[$like->objectUserID]++;
                }
            }

            // delete like notifications
            if (!empty($notificationObjectTypes)) {
                foreach ($notificationObjectTypes as $notificationObjectType) {
                    UserNotificationHandler::getInstance()
                        ->removeNotifications($notificationObjectType, $likeList->getObjectIDs());
                }
            } elseif (UserNotificationHandler::getInstance()->getObjectTypeID($objectType . '.notification')) {
                UserNotificationHandler::getInstance()
                    ->removeNotifications($objectType . '.notification', $likeList->getObjectIDs());
            }

            // revoke activity points
            UserActivityPointHandler::getInstance()
                ->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $activityPoints);

            // delete likes
            (new ReactionAction(\array_keys($likeData), 'delete'))->executeAction();
        }

        // delete like objects
        if (!empty($likeObjectIDs)) {
            (new LikeObjectAction($likeObjectIDs, 'delete'))->executeAction();
        }

        // delete activity events
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType . '.recentActivityEvent')) {
            UserActivityEventHandler::getInstance()
                ->removeEvents($objectTypeObj->objectType . '.recentActivityEvent', $objectIDs);
        }
    }

    /**
     * Returns the first available reaction type.
     */
    public function getFirstReactionType(): ReactionType|false
    {
        static $firstReactionType;

        if ($firstReactionType === null) {
            $reactionTypes = ReactionTypeCache::getInstance()->getReactionTypes();
            ReactionType::sort($reactionTypes, 'showOrder');

            $firstReactionType = \reset($reactionTypes);
        }

        return $firstReactionType;
    }

    /**
     * Returns the first available reaction type's id.
     */
    public function getFirstReactionTypeID(): ?int
    {
        $firstReactionType = $this->getFirstReactionType();

        return $firstReactionType ? $firstReactionType->reactionTypeID : null;
    }

    /**
     * Removes deleted reactions from the reaction counter for the like object table.
     *
     * @param array<int, int> $cachedReactions
     * @return array<int, int>
     */
    private function cleanUpCachedReactions(array $cachedReactions): array
    {
        foreach ($cachedReactions as $reactionTypeID => $count) {
            if (self::getReactionTypeByID($reactionTypeID) === null) {
                unset($cachedReactions[$reactionTypeID]);
            }
        }

        return $cachedReactions;
    }

    /**
     * @return ?array{count: int, other: int, reaction: ?ReactionType}
     */
    public function getTopReaction(?string $cachedReactions): ?array
    {
        if ($cachedReactions) {
            $cachedReactions = @\unserialize($cachedReactions);

            if (\is_array($cachedReactions)) {
                $cachedReactions = self::cleanUpCachedReactions($cachedReactions);

                if (!empty($cachedReactions)) {
                    $allReactions = \array_sum($cachedReactions);

                    \arsort($cachedReactions, \SORT_NUMERIC);

                    $count = \current($cachedReactions);

                    return [
                        'count' => $count,
                        'other' => $allReactions - $count,
                        'reaction' => ReactionTypeCache::getInstance()->getReactionTypeByID(\key($cachedReactions)),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Renders an inline list of reaction counts.
     *
     * @param int[] $reactionCounts format: `[reactionID => count]`
     * @since 5.3
     */
    public function renderInlineList(array $reactionCounts): string
    {
        $reactionsOuput = [];
        foreach ($reactionCounts as $reactionTypeID => $count) {
            $reactionsOuput[] = WCF::getLanguage()->getDynamicVariable('wcf.reactions.reactionTypeCount', [
                'count' => $count,
                'reaction' => $this->getReactionTypeByID($reactionTypeID),
            ]);
        }

        return \implode(', ', $reactionsOuput);
    }
}
