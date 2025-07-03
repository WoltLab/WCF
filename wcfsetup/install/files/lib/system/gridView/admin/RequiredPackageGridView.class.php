<?php

namespace wcf\system\gridView\admin;

use wcf\data\package\I18nPackageList;

/**
 * Grid view for the list of required packages.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class RequiredPackageGridView extends PackageGridView
{
    public function __construct(public readonly int $packageID)
    {
        parent::__construct();
    }

    #[\Override]
    public function getParameters(): array
    {
        return ['packageID' => $this->packageID];
    }

    #[\Override]
    protected function createObjectList(): I18nPackageList
    {
        $list = parent::createObjectList();
        $list->getConditionBuilder()->add(
            'package.packageID IN (SELECT requirement FROM wcf1_package_requirement WHERE packageID = ?)',
            [$this->packageID]
        );

        return $list;
    }
}
