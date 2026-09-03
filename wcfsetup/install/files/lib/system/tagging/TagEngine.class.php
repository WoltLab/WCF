<?php

namespace wcf\system\tagging;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagAction;
use wcf\data\tag\TagList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\language\LanguageFactory;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Manages the tagging of objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TagEngine extends SingletonFactory
{
    /**
     * Adds tags to a tagged object.
     *
     * @param string[] $tags
     * @return void
     */
    public function addObjectTags(string $objectType, int $objectID, array $tags, int $languageID, bool $replace = true)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        $tags = \array_unique(
            \array_reduce(
                ArrayUtil::trim(
                    \array_map(static function ($tag) {
                        return \explode(',', $tag);
                    }, $tags)
                ),
                'array_merge',
                []
            )
        );

        // remove tags prior to apply the new ones (prevents duplicate entries)
        if ($replace) {
            $sql = "DELETE      tag_to_object
                    FROM        wcf1_tag_to_object tag_to_object
                    INNER JOIN  wcf1_tag tag
                            ON  tag.tagID = tag_to_object.tagID
                    WHERE       tag_to_object.objectTypeID = ?
                            AND tag_to_object.objectID = ?
                            AND tag.languageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $objectTypeID,
                $objectID,
                $languageID,
            ]);
        }

        // get tag ids
        $tagIDs = [];
        foreach ($tags as $tag) {
            if (empty($tag)) {
                continue;
            }

            // enforce max length
            if (\mb_strlen($tag) > \TAGGING_MAX_TAG_LENGTH) {
                $tag = \mb_substr($tag, 0, \TAGGING_MAX_TAG_LENGTH);
            }

            // find existing tag
            $tagObj = Tag::getTag($tag, $languageID);
            if ($tagObj === null) {
                // create new tag
                $tagAction = new TagAction([], 'create', [
                    'data' => [
                        'name' => $tag,
                        'languageID' => $languageID,
                    ],
                ]);

                $tagAction->executeAction();
                $returnValues = $tagAction->getReturnValues();
                $tagObj = $returnValues['returnValues'];
            }

            if ($tagObj->synonymFor !== null) {
                $tagIDs[$tagObj->synonymFor] = $tagObj->synonymFor;
            } else {
                $tagIDs[$tagObj->tagID] = $tagObj->tagID;
            }
        }

        // save tags
        $sql = "INSERT INTO wcf1_tag_to_object
                            (objectID, tagID, objectTypeID, languageID)
                VALUES      (?, ?, ?, ?)";
        WCF::getDB()->beginTransaction();
        $statement = WCF::getDB()->prepare($sql);
        foreach ($tagIDs as $tagID) {
            $statement->execute([$objectID, $tagID, $objectTypeID, $languageID]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Deletes all tags assigned to given tagged object.
     *
     * @return void
     */
    public function deleteObjectTags(string $objectType, int $objectID, ?int $languageID = null)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $sql = "DELETE      tag_to_object
                FROM        wcf1_tag_to_object tag_to_object
                INNER JOIN  wcf1_tag tag
                        ON  tag.tagID = tag_to_object.tagID
                WHERE       tag_to_object.objectTypeID = ?
                        AND tag_to_object.objectID = ?
                        " . ($languageID !== null ? "AND tag.languageID = ?" : "");
        $statement = WCF::getDB()->prepare($sql);
        $parameters = [
            $objectTypeID,
            $objectID,
        ];
        if ($languageID !== null) {
            $parameters[] = $languageID;
        }
        $statement->execute($parameters);
    }

    /**
     * Deletes all tags assigned to given tagged objects.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function deleteObjects(string $objectType, array $objectIDs)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $conditionsBuilder = new PreparedStatementConditionBuilder();
        $conditionsBuilder->add('objectTypeID = ?', [$objectTypeID]);
        $conditionsBuilder->add('objectID IN (?)', [$objectIDs]);

        $sql = "DELETE FROM wcf1_tag_to_object
                " . $conditionsBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionsBuilder->getParameters());
    }

    /**
     * Returns all tags set for given object.
     *
     * @param int[] $languageIDs
     * @return  Tag[]
     */
    public function getObjectTags(string $objectType, int $objectID, array $languageIDs = [])
    {
        $tags = $this->getObjectsTags($objectType, [$objectID], $languageIDs);

        return $tags[$objectID] ?? [];
    }

    /**
     * Returns all tags set for given objects.
     *
     * @param int[] $objectIDs
     * @param int[] $languageIDs
     * @return array<int, array<int, Tag>>
     */
    public function getObjectsTags(string $objectType, array $objectIDs, array $languageIDs = [])
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("tag_to_object.objectTypeID = ?", [$objectTypeID]);
        $conditions->add("tag_to_object.objectID IN (?)", [$objectIDs]);
        if ($languageIDs !== []) {
            foreach ($languageIDs as $index => $languageID) {
                if ($languageID === 0) {
                    unset($languageIDs[$index]);
                }
            }

            // The `languageID` is part of the index, skipping it will cause MySQL to skip the
            // `objectID` column, causing a partial table scan.
            if ($languageIDs === []) {
                // The `languageID` column is never null, tags are always assigned to a language
                // thus we cannot use the content language ids here.
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    $languageIDs[] = $language->languageID;
                }
            }

            $conditions->add("tag.languageID IN (?)", [$languageIDs]);
        }

        $sql = "SELECT      tag.*, tag_to_object.objectID
                FROM        wcf1_tag_to_object tag_to_object
                LEFT JOIN   wcf1_tag tag
                ON          tag.tagID = tag_to_object.tagID
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $tags = [];
        while ($tag = $statement->fetchObject(Tag::class)) {
            // @phpstan-ignore property.notFound
            $objectID = $tag->objectID;
            if (!isset($tags[$objectID])) {
                $tags[$objectID] = [];
            }
            $tags[$objectID][$tag->tagID] = $tag;
        }

        return $tags;
    }

    /**
     * Returns id of the object type with the given name.
     *
     * @return  int
     * @throws  InvalidObjectTypeException
     */
    public function getObjectTypeID(string $objectType)
    {
        // get object type
        $objectTypeObj = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.tagging.taggableObject', $objectType);
        if ($objectTypeObj === null) {
            throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.tagging.taggableObject');
        }

        return $objectTypeObj->objectTypeID;
    }

    /**
     * Returns the implicit language id based on the language id of existing tags.
     *
     * NULL indicates that there are no tags, otherwise the language id with the most
     * associated tags for that object is returned, but can still be arbitrary if
     * there are two or more top language ids with the same amount of tags.
     *
     * @return      int|null
     */
    public function getImplicitLanguageID(string $objectType, int $objectID)
    {
        $existingTags = $this->getObjectTags($objectType, $objectID);
        if ($existingTags === []) {
            return null;
        }

        $languageIDs = [];
        foreach ($existingTags as $tag) {
            if (!isset($languageIDs[$tag->languageID])) {
                $languageIDs[$tag->languageID] = 0;
            }
            $languageIDs[$tag->languageID]++;
        }

        \arsort($languageIDs, \SORT_NUMERIC);

        return \key($languageIDs);
    }

    /**
     * @param Tag[] $tags
     * @return int[]
     */
    public function getTagIDs(array $tags)
    {
        return \array_map(static function ($tag) {
            return $tag->tagID;
        }, $tags);
    }

    /**
     * Generates the inner SQL statement to fetch object ids that have all listed
     * tags assigned to them.
     *
     * @param Tag[] $tags
     * @return array{sql: string, parameters: mixed[]}
     * @since   5.2
     */
    public function getSubselectForObjectsByTags(string $objectType, array $tags)
    {
        $parameters = [$this->getObjectTypeID($objectType)];
        $tagIDs = \implode(',', \array_map(static function (Tag $tag) use (&$parameters) {
            $parameters[] = $tag->tagID;

            return '?';
        }, $tags));
        $parameters[] = \count($tags);

        $sql = "SELECT      objectID
                FROM        wcf1_tag_to_object
                WHERE       objectTypeID = ?
                        AND tagID IN (" . $tagIDs . ")
                GROUP BY    objectID
                HAVING  COUNT(objectID) = ?";

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * Generates the inner SQL statement to fetch object ids that have all listed
     * tags assigned to them.
     *
     * @param int[] $tagIDs
     * @return array{sql: string, parameters: mixed[]}
     * @since 6.2
     */
    public function getSubselectForObjectsByTagIDs(string $objectType, array $tagIDs): array
    {
        $parameters = \array_merge([$this->getObjectTypeID($objectType)], $tagIDs);
        $parameters[] = \count($tagIDs);
        $placeholders = \implode(',', \array_map(static fn() => '?', $tagIDs));

        $sql = "SELECT      objectID
                FROM        wcf1_tag_to_object
                WHERE       objectTypeID = ?
                        AND tagID IN (" . $placeholders . ")
                GROUP BY    objectID
                HAVING  COUNT(objectID) = ?";

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * Returns the matching tags by name.
     *
     * @param string[] $names
     * @return Tag[]
     * @since   5.2
     */
    public function getTagsByName(array $names, int $languageID)
    {
        $tagList = new TagList();
        $tagList->getConditionBuilder()->add('name IN (?)', [$names]);
        $tagList->getConditionBuilder()->add('languageID = ?', [$languageID ?: WCF::getLanguage()->languageID]);
        $tagList->readObjects();

        return $tagList->getObjects();
    }
}
