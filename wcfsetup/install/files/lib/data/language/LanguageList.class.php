<?php

namespace wcf\data\language;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of languages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Language    current()
 * @method  Language[]  getObjects()
 * @method  Language|null   getSingleObject()
 * @method  Language|null   search($objectID)
 * @property    Language[] $objects
 */
class LanguageList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Language::class;
}
