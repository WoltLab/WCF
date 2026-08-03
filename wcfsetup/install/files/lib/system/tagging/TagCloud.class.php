<?php

namespace wcf\system\tagging;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\TagCloudTag;
use wcf\system\cache\tolerant\TagCloudCache;
use wcf\system\language\LanguageFactory;

/**
 * This class holds a list of tags that can be used for creating a tag cloud.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TagCloud
{
    /**
     * list of tags
     * @var TagCloudTag[]
     */
    protected $tags = [];

    /**
     * max value of tag counter
     * @var int
     */
    protected $maxCounter = 0;

    /**
     * min value of tag counter
     * @var int
     */
    protected $minCounter = 4294967295;

    /**
     * active language ids
     * @var int[]
     */
    protected $languageIDs = [];

    /**
     * Constructs a new TagCloud object.
     *
     * @param int[] $languageIDs
     */
    public function __construct(array $languageIDs = [])
    {
        $this->languageIDs = $languageIDs;
        if (empty($this->languageIDs)) {
            $this->languageIDs = \array_keys(LanguageFactory::getInstance()->getLanguages());
        }

        // init cache
        $this->loadCache();
    }

    /**
     * Loads the tag cloud cache.
     *
     * @return void
     */
    protected function loadCache()
    {
        $objectTypeIDs = \array_map(
            static fn(ObjectType $objectType) => $objectType->objectTypeID,
            ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject')
        );

        $this->tags = (new TagCloudCache($objectTypeIDs, $this->languageIDs))->getCache();
    }

    /**
     * Returns a list of weighted tags.
     *
     * @return  TagCloudTag[]   the tags to get
     */
    public function getTags(int $slice = 50)
    {
        // slice list
        /** @var TagCloudTag[] $tags */
        $tags = \array_slice($this->tags, 0, \min($slice, \count($this->tags)));

        // get min / max counter
        foreach ($tags as $tag) {
            if ($tag->counter > $this->maxCounter) {
                $this->maxCounter = $tag->counter;
            }
            if ($tag->counter < $this->minCounter) {
                $this->minCounter = $tag->counter;
            }
        }

        // assign sizes
        foreach ($tags as $tag) {
            $tag->setWeight($this->calculateWeight($tag->counter));
        }

        // sort alphabetically
        \ksort($tags, \SORT_NATURAL | \SORT_FLAG_CASE);

        // return tags
        return $tags;
    }

    /**
     * Calculates the weight of the tag based on the given tag count.
     *
     * @return  float|int
     */
    private function calculateWeight(int $counter)
    {
        if ($this->maxCounter === $this->minCounter) {
            return 2;
        } else {
            $weight = \round(\log($counter) / \log($this->maxCounter) * 7);
            if ($weight < 1) {
                $weight = 1;
            }

            return $weight;
        }
    }
}
