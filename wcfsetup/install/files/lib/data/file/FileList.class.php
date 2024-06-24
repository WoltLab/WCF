<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectList;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method File current()
 * @method File[] getObjects()
 * @method File|null getSingleObject()
 * @method File|null search($objectID)
 * @property File[] $objects
 */
class FileList extends DatabaseObjectList
{
    public $className = File::class;
}
