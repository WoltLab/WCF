<?php

namespace wcf\data\spider;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of spiders.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Spider
 *
 * @method  Spider      current()
 * @method  Spider[]    getObjects()
 * @method  Spider|null search($objectID)
 * @property    Spider[] $objects
 */
class SpiderList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Spider::class;
}
