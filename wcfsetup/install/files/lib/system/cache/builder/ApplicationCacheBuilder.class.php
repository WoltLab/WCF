<?php

namespace wcf\system\cache\builder;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\WCF;

/**
 * Caches applications.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ApplicationCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [
            'abbreviation' => [],
            'application' => [],
        ];

        // fetch applications
        $sql = "SELECT *
                FROM   wcf" . WCF_N . "_application";
        $statement = WCF::getDB()->prepareUnmanaged($sql);
        $statement->execute();
        $applications = $statement->fetchObjects(Application::class);

        foreach ($applications as $application) {
            $data['application'][$application->packageID] = $application;
        }

        // fetch abbreviations
        $sql = "SELECT packageID, package
                FROM   wcf" . WCF_N . "_package
                WHERE  isApplication = ?";
        $statement = WCF::getDB()->prepareUnmanaged($sql);
        $statement->execute([1]);
        $packages = $statement->fetchMap('packageID', 'package');
        foreach ($packages as $packageID => $package) {
            $data['abbreviation'][Package::getAbbreviation($package)] = $packageID;
        }

        return $data;
    }
}
