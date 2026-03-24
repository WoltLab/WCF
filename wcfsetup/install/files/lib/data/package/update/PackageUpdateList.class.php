<?php

namespace wcf\data\package\update;

use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents a list of package updates.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<PackageUpdate>
 */
class PackageUpdateList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = PackageUpdate::class;

    /**
     * @param bool $useSqlOr
     */
    #[\Override]
    public function __construct($useSqlOr = false)
    {
        parent::__construct();

        if ($useSqlOr) {
            $this->conditionBuilder = new PreparedStatementConditionBuilder(true, 'OR');
        }
    }
}
