<?php

namespace wcf\data\devtools\project;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of devtools projects.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<DevtoolsProject>
 */
class DevtoolsProjectList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = DevtoolsProject::class;
}
