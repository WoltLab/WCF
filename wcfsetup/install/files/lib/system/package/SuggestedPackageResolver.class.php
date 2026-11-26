<?php

namespace wcf\system\package;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Collects the identifiers of the packages suggested by installed packages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.3
 */
final class SuggestedPackageResolver
{
    /**
     * Returns the packages that the active package update servers suggest for the installed
     * version of the given packages.
     *
     * @param list<int> $packageIDs
     * @return list<string>
     */
    public function getStoredSuggestions(array $packageIDs): array
    {
        if ($packageIDs === []) {
            return [];
        }

        $updateServers = PackageUpdateServer::getActiveUpdateServers();
        if ($updateServers === []) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package.packageID IN (?)", [$packageIDs]);
        $conditions->add("package_update.packageUpdateServerID IN (?)", [\array_keys($updateServers)]);

        $sql = "SELECT      DISTINCT package_update_suggestion.package
                FROM        wcf1_package package
                INNER JOIN  wcf1_package_update package_update
                ON          package_update.package = package.package
                INNER JOIN  wcf1_package_update_version package_update_version
                ON          package_update_version.packageUpdateID = package_update.packageUpdateID
                        AND package_update_version.packageVersion = package.packageVersion
                INNER JOIN  wcf1_package_update_suggestion package_update_suggestion
                ON          package_update_suggestion.packageUpdateVersionID = package_update_version.packageUpdateVersionID
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        return $this->merge($statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * Merges lists of package identifiers, case-insensitively. The first spelling of an
     * identifier is kept.
     *
     * @param list<string> ...$lists
     * @return list<string>
     */
    public function merge(array ...$lists): array
    {
        $identifiers = [];
        foreach ($lists as $list) {
            foreach ($list as $identifier) {
                $identifiers[\strtolower($identifier)] ??= $identifier;
            }
        }

        return \array_values($identifiers);
    }

    /**
     * Removes the identifiers of installed packages.
     *
     * @param list<string> $identifiers
     * @return list<string>
     */
    public function removeInstalled(array $identifiers): array
    {
        if ($identifiers === []) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [$identifiers]);

        $sql = "SELECT  package
                FROM    wcf1_package
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $installedPackages = \array_map(\strtolower(...), $statement->fetchAll(\PDO::FETCH_COLUMN));

        return \array_values(\array_filter(
            $identifiers,
            static fn(string $identifier) => !\in_array(\strtolower($identifier), $installedPackages, true)
        ));
    }
}
