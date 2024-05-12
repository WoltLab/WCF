<?php

namespace wcf\system\event\listener;

use wcf\event\package\PackageUpdateListChanged;
use wcf\system\package\license\LicenseApi;

/**
 * Updates the license data when updating the package list.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class PackageUpdateListChangedLicenseListener
{
    private readonly LicenseApi $licenseApi;

    public function __construct()
    {
        $this->licenseApi = new LicenseApi();
    }

    public function __invoke(PackageUpdateListChanged $event): void
    {
        if (!$event->updateServer->isWoltLabUpdateServer()) {
            return;
        }

        try {
            $licenseData = $this->licenseApi->fetchFromRemote();
            $this->licenseApi->updateLicenseFile($licenseData);
        } catch (\Throwable) {
            // This is a “silent” operation that should not interrupt the
            // execution of the package list update.
        }
    }
}
