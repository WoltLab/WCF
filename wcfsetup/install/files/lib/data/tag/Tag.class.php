<?php

namespace wcf\data\tag;

use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Represents a tag.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $tagID      unique id of the tag
 * @property-read   int $languageID id of the language the tag belongs to
 * @property-read   string $name       name/text of the tag
 * @property-read   int|null $synonymFor id of the tag for which the tag is a synonym or `null` if the tag is no synonym
 */
class Tag extends DatabaseObject implements IRouteController
{
    /**
     * Return the tag with the given name or null of no such tag exists.
     *
     * @param string $name
     * @param int $languageID
     * @return  Tag|null
     */
    public static function getTag($name, $languageID = 0)
    {
        $sql = "SELECT  *
                FROM    wcf1_tag
                WHERE   languageID = ?
                    AND name = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$languageID, $name]);

        return $statement->fetchObject(self::class);
    }

    /**
     * Takes a string of comma separated tags and splits it into an array.
     *
     * @param string $tags
     * @param string $separators
     * @return  string[]
     */
    public static function splitString($tags, $separators = ',;')
    {
        return \array_unique(ArrayUtil::trim(\preg_split('/[' . \preg_quote($separators) . ']/', $tags)));
    }

    /**
     * Takes a list of tags and builds a comma separated string from it.
     *
     * @param mixed[] $tags
     * @param string $separator
     * @return  string
     */
    public static function buildString(array $tags, $separator = ', ')
    {
        return \implode($separator, $tags);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->name;
    }

    /**
     * Returns the name of this tag.
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }
}
