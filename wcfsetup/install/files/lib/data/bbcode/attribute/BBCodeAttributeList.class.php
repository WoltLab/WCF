<?php

namespace wcf\data\bbcode\attribute;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of bbcode attribute.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  BBCodeAttribute     current()
 * @method  BBCodeAttribute[]   getObjects()
 * @method  BBCodeAttribute|null    getSingleObject()
 * @method  BBCodeAttribute|null    search($objectID)
 * @property    BBCodeAttribute[] $objects
 */
class BBCodeAttributeList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = BBCodeAttribute::class;
}
