<?php

namespace wcf\data\bbcode;

use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\system\cache\builder\BBCodeCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the bbcode cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodeCache extends SingletonFactory
{
    /**
     * cached bbcodes
     * @var BBCode[]
     */
    protected $cachedBBCodes = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get bbcode cache
        $this->cachedBBCodes = BBCodeCacheBuilder::getInstance()->getData([], 'bbcodes');
    }

    /**
     * Returns all bbcodes.
     *
     * @return  BBCode[]
     */
    public function getBBCodes()
    {
        return $this->cachedBBCodes;
    }

    /**
     * Returns the BBCode with the given tag or `null` if no such BBCode exists.
     *
     * @param string $tag
     * @return  BBCode|null
     */
    public function getBBCodeByTag($tag)
    {
        return $this->cachedBBCodes[$tag] ?? null;
    }

    /**
     * Returns all attributes of a bbcode.
     *
     * @param string $tag
     * @return  BBCodeAttribute[]
     */
    public function getBBCodeAttributes($tag)
    {
        return $this->cachedBBCodes[$tag]->getAttributes();
    }
}
