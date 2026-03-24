<?php

namespace wcf\data\package\update\server;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PackageUpdateCacheBuilder;

/**
 * Provides functions to edit package update servers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       PackageUpdateServer
 * @extends DatabaseObjectEditor<PackageUpdateServer>
 * @implements IEditableCachedObject<PackageUpdateServer>
 */
class PackageUpdateServerEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PackageUpdateServer::class;

    #[\Override]
    public static function resetCache()
    {
        PackageUpdateCacheBuilder::getInstance()->reset();
    }
}
