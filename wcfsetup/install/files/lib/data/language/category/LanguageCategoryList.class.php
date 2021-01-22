<?php

namespace wcf\data\language\category;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of language categories.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Language\Category
 *
 * @method  LanguageCategory    current()
 * @method  LanguageCategory[]  getObjects()
 * @method  LanguageCategory|null   search($objectID)
 * @property    LanguageCategory[] $objects
 */
class LanguageCategoryList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = LanguageCategory::class;
}
