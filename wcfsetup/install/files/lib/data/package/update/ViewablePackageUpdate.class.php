<?php

namespace wcf\data\package\update;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\version\PackageUpdateVersion;

/**
 * Provides a viewable package update object.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   PackageUpdate
 * @extends DatabaseObjectDecorator<PackageUpdate>
 */
class ViewablePackageUpdate extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PackageUpdate::class;

    /**
     * latest accessible package update version object
     * @var ?PackageUpdateVersion
     */
    protected $accessibleVersion;

    /**
     * latest package update version object
     * @var ?PackageUpdateVersion
     */
    protected $latestVersion;

    /**
     * @var ?PackageUpdateServer
     */
    protected $updateServer;

    /**
     * Sets latest accessible package update version object.
     *
     * @return void
     */
    public function setAccessibleVersion(PackageUpdateVersion $latestVersion)
    {
        $this->accessibleVersion = $latestVersion;
    }

    /**
     * Sets latest package update version object.
     *
     * @return void
     */
    public function setLatestVersion(PackageUpdateVersion $latestVersion)
    {
        $this->latestVersion = $latestVersion;
    }

    /**
     * Returns latest accessible package update version object.
     *
     * @return PackageUpdateVersion
     */
    public function getAccessibleVersion()
    {
        return $this->accessibleVersion;
    }

    /**
     * Returns latest package update version object.
     *
     * @return PackageUpdateVersion
     */
    public function getLatestVersion()
    {
        return $this->latestVersion;
    }

    /**
     * @return void
     * @since 5.2
     */
    public function setUpdateServer(PackageUpdateServer $updateServer)
    {
        $this->updateServer = $updateServer;
    }

    /**
     * @return PackageUpdateServer
     * @since 5.2
     */
    public function getUpdateServer()
    {
        return $this->updateServer;
    }
}
