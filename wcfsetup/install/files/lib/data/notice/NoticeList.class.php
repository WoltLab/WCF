<?php

namespace wcf\data\notice;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of notices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Notice
 *
 * @method  Notice      current()
 * @method  Notice[]    getObjects()
 * @method  Notice|null getSingleObject()
 * @method  Notice|null seach($objectID)
 * @property    Notice[] $objects
 */
class NoticeList extends DatabaseObjectList
{
}
