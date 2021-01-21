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
 * @package WoltLabSuite\Core\Data\Bbcode
 */
class BBCodeCache extends SingletonFactory
{
    /**
     * cached bbcodes
     * @var BBCode[]
     */
    protected $cachedBBCodes = [];

    /**
     * list of known highlighters
     * @var string[]
     * @deprecated  since 5.2, use Prism to highlight your code.
     */
    protected $highlighters = [];

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
     * @param   string      $tag
     * @return  BBCode|null
     */
    public function getBBCodeByTag($tag)
    {
        if (isset($this->cachedBBCodes[$tag])) {
            return $this->cachedBBCodes[$tag];
        }
    }

    /**
     * Returns all attributes of a bbcode.
     *
     * @param   string      $tag
     * @return  BBCodeAttribute[]
     */
    public function getBBCodeAttributes($tag)
    {
        return $this->cachedBBCodes[$tag]->getAttributes();
    }

    /**
     * Returns a list of known highlighters.
     *
     * @return  string[]
     * @deprecated  since 5.2, use Prism to highlight your code.
     */
    public function getHighlighters()
    {
        if (empty($this->highlighters)) {
            $this->highlighters = BBCodeCacheBuilder::getInstance()->getData([], 'highlighters');
        }

        return $this->highlighters;
    }
}
