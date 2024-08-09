<?php

namespace wcf\system\package;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\exception\UserException;
use wcf\system\WCF;

/**
 * Handles the case that the credentials for update server are either missing or invalid.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class PackageUpdateUnauthorizedException extends UserException
{
    /**
     * @param string[] $responseHeaders
     * @param mixed[] $packageUpdateVersion
     */
    public function __construct(
        private readonly int $responseStatusCode,
        private readonly array $responseHeaders,
        private readonly string $responseMessage,
        private readonly PackageUpdateServer $updateServer,
        private readonly array $packageUpdateVersion = []
    ) {
    }

    /**
     * Returns the rendered template.
     */
    public function getRenderedTemplate(): string
    {
        $requiresPaidUpgrade = false;
        if ($this->updateServer->isWoltLabStoreServer() && !empty($this->packageUpdateVersion['pluginStoreFileID'])) {
            $requiresPaidUpgrade = ($this->responseHeaders['wcf-update-server-requires-paid-upgrade'][0] ?? '') === 'true';
        }

        if ($requiresPaidUpgrade) {
            WCF::getTPL()->assign([
                'packageName' => $this->packageUpdateVersion['packageName'],
                'pluginStoreFileID' => $this->packageUpdateVersion['pluginStoreFileID'],
            ]);

            return WCF::getTPL()->fetch('packageUpdateUnauthorizedPaidUpgrade');
        }

        $authInsufficient = (($this->responseHeaders['wcf-update-server-auth'][0] ?? '') === 'unauthorized');
        if ($authInsufficient && !empty($this->packageUpdateVersion['pluginStoreFileID'])) {
            $hasOnlyTrustedServers = true;
            foreach (PackageUpdateServer::getActiveUpdateServers() as $updateServer) {
                if (!$updateServer->isWoltLabUpdateServer() && !$updateServer->isWoltLabStoreServer()) {
                    $hasOnlyTrustedServers = false;
                    break;
                }
            }

            if ($hasOnlyTrustedServers) {
                WCF::getTPL()->assign([
                    'packageName' => $this->packageUpdateVersion['packageName'],
                    'pluginStoreFileID' => $this->packageUpdateVersion['pluginStoreFileID'],
                ]);

                return WCF::getTPL()->fetch('packageUpdateUnauthorizedPurchaseRequired');
            }
        }

        WCF::getTPL()->assign([
            'authInsufficient' => $authInsufficient,
            'packageUpdateVersion' => $this->packageUpdateVersion,
            'updateServer' => $this->updateServer,
            'serverAuthData' => $this->updateServer->getAuthData(),
            'requiresPaidUpgrade' => $requiresPaidUpgrade,
            'responseStatusCode' => $this->responseStatusCode,
            'responseHeaders' => $this->responseHeaders,
            'responseMessage' => $this->responseMessage,
        ]);

        return WCF::getTPL()->fetch('packageUpdateUnauthorized');
    }

    public function getResponseMessage(): string
    {
        return $this->responseMessage;
    }
}
