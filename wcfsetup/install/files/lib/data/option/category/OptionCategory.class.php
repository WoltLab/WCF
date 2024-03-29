<?php

namespace wcf\data\option\category;

use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;

/**
 * Represents an option category.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $categoryID     unique id of the option category
 * @property-read   int $packageID      id of the package the which delivers the option category
 * @property-read   string $categoryName       name and textual identifier of the option category
 * @property-read   string $parentCategoryName name of the option category's parent category or empty if the option category has no parent category
 * @property-read   int $showOrder      position of the option category in relation its siblings
 * @property-read   string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to see the option category
 * @property-read   string $options        comma separated list of options of which at least one needs to be enabled for the option category to be shown
 */
class OptionCategory extends DatabaseObject
{
    use TDatabaseObjectOptions;
    use TDatabaseObjectPermissions;
}
