<?php

/**
 * Validates the license credentials and writes the license file.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\system\package\license\LicenseApi;

try {
    // Side-effect: Writes the license file.
    LicenseApi::fetchFromRemote();

    // If we’re still here it means that the credentials are actually valid. Now
    // we can check if the credentials for both servers are in sync, because
    // traditionally users could use their account credentials to authenticate.
    $updateServer = PackageUpdateServer::getWoltLabUpdateServer();
    $storeServer = PackageUpdateServer::getPluginStoreServer();

    if ($updateServer->getAuthData() !== $storeServer->getAuthData()) {
        $authData = $updateServer->getAuthData();

        (new PackageUpdateServerEditor($storeServer))->update([
            'username' => $authData['username'],
            'password' => $authData['password'],
        ]);
    }
} catch (\Throwable) {
    // This action must be silent, failing to execute is not an issue here.
}
