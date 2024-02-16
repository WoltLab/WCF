<?php

namespace wcf\data\file\temporary;

use wcf\data\DatabaseObjectList;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method FileTemporary current()
 * @method FileTemporary[] getObjects()
 * @method FileTemporary|null getSingleObject()
 * @method FileTemporary|null search($objectID)
 * @property FileTemporary[] $objects
 */
class FileTemporaryList extends DatabaseObjectList
{
    public $className = FileTemporary::class;
}
