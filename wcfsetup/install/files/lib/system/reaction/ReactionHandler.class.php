<?php

namespace wcf\system\reaction;

use wcf\command\reaction\DeleteObjectReactions;
use wcf\command\reaction\SetReaction;
use wcf\command\reaction\RevertReaction;
use wcf\data\DatabaseObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\Like;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;
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
     * @var array<int, array<int, ?LikeObject>>
     */
    private array $likeObjectCache = [];

    /**
     * @var ObjectType[]
     */
    private array $cache;

    /**
     * @var ILikeObject[][]
     * @deprecated 6.3
     */
    private array $likeableObjectsCache = [];

    #[\Override]
    protected function init(): void
    {
        $this->cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.like.likeableObject');
    }

    /**
     * Returns the JSON encoded JavaScript variable for the template.
     */
    public function getReactionsJSVariable(): string
    {
        $returnValues = [];

        foreach ($this->getReactionTypes() as $reaction) {
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
     *
     * @deprecated 6.3
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
     *
     * @deprecated 6.3
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
     *
     * @deprecated 6.3
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
        if (
            !isset($this->likeObjectCache[$objectType->objectTypeID])
            || !\array_key_exists($objectID, $this->likeObjectCache[$objectType->objectTypeID])
        ) {
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
            // Use `array_filter` to filter `null` values.
            return \array_filter($this->likeObjectCache[$objectType->objectTypeID]);
        }

        return [];
    }

    /**
     * Loads the like data for a set of objects and returns the number of loaded
     * like objects.
     *
     * @param list<int> $objectIDs
     */
    public function loadLikeObjects(ObjectType $objectType, array $objectIDs, bool $loadLikeableObjects = true): int
    {
        if ($objectIDs === []) {
            return 0;
        }

        if ($loadLikeableObjects) {
            $this->cacheLikeableObjects($objectType->objectType, $objectIDs);
        }

        $i = 0;

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("like_object.objectTypeID = ?", [$objectType->objectTypeID]);
        $conditions->add("like_object.objectID IN (?)", [$objectIDs]);
        $parameters = $conditions->getParameters();

        if (!WCF::getUser()->isGuest()) {
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

        foreach ($objectIDs as $objectID) {
            $this->likeObjectCache[$objectType->objectTypeID][$objectID] ??= null;
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
     * @deprecated 6.3 Use `SetReaction` command instead.
     */
    public function react(ILikeObject $likeable, User $user, int $reactionTypeID, int $time = \TIME_NOW): array
    {
        // verify if object is already liked by user
        $like = Like::getLike($likeable->getObjectType()->objectTypeID, $likeable->getObjectID(), $user->userID);

        // get like object
        $likeObject = LikeObject::getLikeObject($likeable->getObjectType()->objectTypeID, $likeable->getObjectID());

        // if vote is identically just revert the vote
        if ($like->likeID !== 0 && ($like->reactionTypeID === $reactionTypeID)) {
            return $this->revertReact($like, $likeable, $likeObject, $user);
        }

        $reaction = ReactionTypeCache::getInstance()->getReactionTypeByID($reactionTypeID);

        new SetReaction($likeable, $user, $reaction)();

        $like = Like::getLike($likeable->getObjectType()->objectTypeID, $likeable->getObjectID(), $user->userID);
        $likeObject = LikeObject::getLikeObject($likeable->getObjectType()->objectTypeID, $likeable->getObjectID());

        return [
            'cachedReactions' => $likeObject->getCachedReactions(),
            'reactionTypeID' => $reactionTypeID,
            'like' => $like,
            'likeObject' => $likeObject,
            'cumulativeLikes' => $likeObject->cumulativeLikes,
        ];
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
     * @deprecated 6.3 Use `RevertReaction` command instead.
     */
    public function revertReact(Like $like, ILikeObject $likeable, LikeObject $likeObject, User $user): array
    {
        if ($like->isNil()) {
            throw new \InvalidArgumentException('The given parameter $like is invalid.');
        }

        new RevertReaction($like, $likeable)();

        $likeObject = LikeObject::getLikeObject($likeable->getObjectType()->objectTypeID, $likeable->getObjectID());

        return [
            'cachedReactions' => $likeObject->getCachedReactions(),
            'reactionTypeID' => null,
            'likeObject' => $likeObject,
            'cumulativeLikes' => $likeObject->cumulativeLikes,
        ];
    }

    /**
     * Removes all reactions for given objects.
     *
     * @param int[] $objectIDs
     * @param string[] $notificationObjectTypes
     * @deprecated 6.3 Use `DeleteObjectReactions` command instead.
     */
    public function removeReactions(string $objectType, array $objectIDs, array $notificationObjectTypes = []): void
    {
        new DeleteObjectReactions(
            $objectType,
            $objectIDs,
            $notificationObjectTypes
        )();
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
    public function getTopReaction(?string $cachedReactionsJson): ?array
    {
        if ($cachedReactionsJson !== null && $cachedReactionsJson !== '') {
            $cachedReactions = \json_decode($cachedReactionsJson, true, flags: \JSON_THROW_ON_ERROR);

            if (\is_array($cachedReactions)) {
                $cachedReactions = self::cleanUpCachedReactions($cachedReactions);

                if ($cachedReactions !== []) {
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
