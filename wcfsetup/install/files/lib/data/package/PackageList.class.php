<?php

namespace wcf\data\package;

use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of packages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Package
 *
 * @method  Package     current()
 * @method  Package[]   getObjects()
 * @method  Package|null    getSingleObject()
 * @method  Package|null    search($objectID)
 * @property    Package[] $objects
 */
class PackageList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Package::class;

    /**
     * Returns a topologically list of all installed packages.
     *
     * Packages listed in a later "group" of the outer array depend on at least
     * one package in an earlier group, but do not depend on any package within
     * the same or later group.
     *
     * @return Package[][]
     * @since 6.0
     */
    public static function getTopologicallySortedPackages(): array
    {
        $list = new self();
        $list->readObjects();
        $pending = $list->getObjects();

        $sql = "SELECT  packageID, requirement
                FROM    wcf1_package_requirement";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $requirementMap = $statement->fetchMap('packageID', 'requirement', false);

        $result = [];
        $handled = [];

        while ($pending !== []) {
            $newResult = [];
            $newPending = [];

            foreach ($pending as $package) {
                $allFulfilled = \array_diff(
                    $requirementMap[$package->packageID] ?? [],
                    $handled
                ) === [];

                if ($allFulfilled) {
                    $newResult[] = $package;
                } else {
                    $newPending[] = $package;
                }
            }

            \array_push($handled, ...\array_column($newResult, 'packageID'));

            $pending = $newPending;
            $result[] = $newResult;
        }

        return $result;
    }
}
