<?php

namespace wcf\data\package\update\server;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package update servers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<PackageUpdateServer>
 */
class PackageUpdateServerList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = PackageUpdateServer::class;

    #[\Override]
    public function readObjects()
    {
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "(
            SELECT  COUNT(*)
            FROM    wcf1_package_update
            WHERE   packageUpdateServerID = " . $this->getDatabaseTableAlias() . ".packageUpdateServerID
        ) AS packages";

        parent::readObjects();
    }
}
