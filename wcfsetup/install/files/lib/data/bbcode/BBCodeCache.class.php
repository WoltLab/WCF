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
     * @var array<string, BBCode>
     */
    protected $cachedBBCodes = [];

    #[\Override]
    protected function init()
    {
        $this->cachedBBCodes = BBCodeCacheBuilder::getInstance()->getData([], 'bbcodes');
    }

    /**
     * Returns all bbcodes.
     *
     * @return array<string, BBCode>
     */
    public function getBBCodes()
    {
        return $this->cachedBBCodes;
    }

    /**
     * Returns the BBCode with the given tag or `null` if no such BBCode exists.
     *
     * @return ?BBCode
     */
    public function getBBCodeByTag(string $tag)
    {
        return $this->cachedBBCodes[$tag] ?? null;
    }

    /**
     * Returns all attributes of a bbcode.
     *
     * @return list<BBCodeAttribute>
     */
    public function getBBCodeAttributes(string $tag)
    {
        return $this->cachedBBCodes[$tag]->getAttributes();
    }
}
