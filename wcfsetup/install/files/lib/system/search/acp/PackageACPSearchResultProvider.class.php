<?php

namespace wcf\system\search\acp;

use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for packages.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageACPSearchResultProvider implements IACPSearchResultProvider
{
    /**
     * @inheritDoc
     */
    public function search($query)
    {
        if (
            !WCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage')
            && !WCF::getSession()->getPermission('admin.configuration.package.canUninstallPackage')
        ) {
            return [];
        }

        $results = [];

        // search by language item
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("languageID = ?", [WCF::getLanguage()->languageID]);
        $conditions->add("languageItem LIKE ?", ['wcf.acp.package.packageName.package%']);
        $conditions->add("languageItemValue LIKE ?", ['%' . $query . '%']);

        $sql = "SELECT  languageItem
                FROM    wcf1_language_item
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $packageIDs = [];
        while ($row = $statement->fetchArray()) {
            $packageIDs[] = \str_replace('wcf.acp.package.packageName.package', '', $row['languageItem']);
        }

        $conditions = new PreparedStatementConditionBuilder(false);
        if (!empty($packageIDs)) {
            $conditions->add("packageID IN (?)", [$packageIDs]);
        }

        $sql = "SELECT  *
                FROM    wcf1_package
                WHERE   packageName LIKE ?
                     OR package LIKE ?
                    " . (\count($conditions->getParameters()) ? "OR " . $conditions : "");
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([
            '%' . $query . '%',
            '%' . $query . '%',
        ], $conditions->getParameters()));

        /** @var Package $package */
        while ($package = $statement->fetchObject(Package::class)) {
            $results[] = new ACPSearchResult($package->getName(), $package->getLink());
        }

        return $results;
    }
}
