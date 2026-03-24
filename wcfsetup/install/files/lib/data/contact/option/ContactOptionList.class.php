<?php

namespace wcf\data\contact\option;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of contact options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<ContactOption>
 */
class ContactOptionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ContactOption::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'showOrder';
}
