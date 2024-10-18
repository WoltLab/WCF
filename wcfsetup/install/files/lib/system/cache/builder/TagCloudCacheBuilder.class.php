<?php

namespace wcf\system\cache\builder;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagCloudTag;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the tag cloud.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TagCloudCacheBuilder extends AbstractCacheBuilder
{
    /**
     * list of tags
     * @var TagCloudTag[]
     */
    protected $tags = [];

    /**
     * language ids
     * @var int
     */
    protected $languageIDs = [];

    /**
     * @inheritDoc
     */
    protected $maxLifetime = 3600;

    /**
     * object type ids
     * @var int
     */
    protected $objectTypeIDs = [];

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $this->languageIDs = $this->parseLanguageIDs($parameters);

        // get all taggable types
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
        foreach ($objectTypes as $objectType) {
            $this->objectTypeIDs[] = $objectType->objectTypeID;
        }

        // get tags
        $this->getTags();

        return $this->tags;
    }

    /**
     * Parses a list of language ids. If one given language id evaluates to '0' all ids will be discarded.
     *
     * @param int[] $parameters
     * @return  int[]
     */
    protected function parseLanguageIDs(array $parameters)
    {
        // handle special '0' value
        if (\in_array(0, $parameters)) {
            // discard all language ids
            $parameters = [];
        }

        return $parameters;
    }

    /**
     * Reads associated tags.
     */
    protected function getTags()
    {
        $this->tags = [];

        if (!empty($this->objectTypeIDs)) {
            // get tag ids
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('object.objectTypeID IN (?)', [$this->objectTypeIDs]);
            $conditionBuilder->add('tag.languageID IN (?)', [$this->languageIDs]);
            $sql = "SELECT      tag.tagID, COUNT(*) AS counter
                    FROM        wcf1_tag_to_object object
                    INNER JOIN  wcf1_tag tag
                            ON  tag.tagID = object.tagID
                    " . $conditionBuilder . "
                    GROUP BY    tag.tagID
                    ORDER BY    counter DESC";
            $statement = WCF::getDB()->prepare($sql, 500);
            $statement->execute($conditionBuilder->getParameters());
            $tagIDs = $statement->fetchMap('tagID', 'counter');

            // get tags
            if (!empty($tagIDs)) {
                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('tagID IN (?)', [\array_keys($tagIDs)]);
                $sql = "SELECT  *
                        FROM    wcf1_tag
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
                while ($row = $statement->fetchArray()) {
                    $row['counter'] = $tagIDs[$row['tagID']];
                    $this->tags[$row['name']] = new TagCloudTag(new Tag(null, $row));
                }

                // sort by counter
                \uasort($this->tags, self::compareTags(...));
            }
        }
    }

    /**
     * Compares the weight between two tags.
     *
     * @param TagCloudTag $tagA
     * @param TagCloudTag $tagB
     * @return  int
     */
    protected static function compareTags($tagA, $tagB)
    {
        if ($tagA->counter > $tagB->counter) {
            return -1;
        }
        if ($tagA->counter < $tagB->counter) {
            return 1;
        }

        return 0;
    }
}
