<?php

namespace wcf\data\option;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Option      current()
 * @method  Option[]    getObjects()
 * @method  Option|null getSingleObject()
 * @method  Option|null search($objectID)
 * @property    Option[] $objects
 */
class OptionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Option::class;
}
