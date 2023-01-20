<?php

namespace wcf\data\acp\search\provider;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP search providers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ACPSearchProvider       current()
 * @method  ACPSearchProvider[]     getObjects()
 * @method  ACPSearchProvider|null      getSingleObject()
 * @method  ACPSearchProvider|null      search($objectID)
 * @property    ACPSearchProvider[] $objects
 */
class ACPSearchProviderList extends DatabaseObjectList
{
}
