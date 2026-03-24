<?php

namespace wcf\data\package;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PackageCacheBuilder;

/**
 * Provides functions to edit packages.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       Package
 * @extends DatabaseObjectEditor<Package>
 * @implements IEditableCachedObject<Package>
 */
class PackageEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Package::class;

    #[\Override]
    public static function resetCache()
    {
        PackageCacheBuilder::getInstance()->reset();
    }
}
