<?php

namespace wcf\data\acp\template;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP templates.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ACPTemplate     current()
 * @method  ACPTemplate[]       getObjects()
 * @method  ACPTemplate|null    getSingleObject()
 * @method  ACPTemplate|null    search($objectID)
 * @property    ACPTemplate[] $objects
 */
class ACPTemplateList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ACPTemplate::class;
}
