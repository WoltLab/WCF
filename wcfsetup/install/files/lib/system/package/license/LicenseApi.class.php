<?php

namespace wcf\system\package\license;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Request;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\io\HttpFactory;

/**
 * Provides access to the license data.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class LicenseApi
{
    private readonly PackageUpdateServer $packageUpdateServer;

    public function __construct()
    {
        $this->packageUpdateServer = PackageUpdateServer::getWoltLabUpdateServer();
    }

    public function fetchLicenseData(): array|object
    {
        if (!$this->hasLicenseCredentials()) {
            // TODO
            throw new \Exception("no credentials");
        }

        $authData = $this->packageUpdateServer->getAuthData();

        $request = new Request(
            'POST',
            'https://api.woltlab.com/2.1/customer/license/list.json',
            [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            \http_build_query([
                'licenseNo' => $authData['username'],
                'serialNo' => $authData['password'],
                'instanceId' => \hash_hmac('sha256', 'api.woltlab.com', \WCF_UUID),
            ], '', '&', \PHP_QUERY_RFC1738)
        );

        $response = HttpFactory::makeClientWithTimeout(5)->send($request);
        return (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->mapper()
            ->map(
                <<<'EOT'
                    array {
                        status: 200,
                        license: array {
                            authCode?: string,
                            licenseID?: int,
                            type: string,
                            expiryDates?: array<string, int>,
                        },
                        pluginstore: array<string, string>,
                        woltlab: array<string, string>,
                    }
                    EOT,
                Source::json($response->getBody())
            );
    }

    public function hasLicenseCredentials(): bool
    {
        $authData = $this->packageUpdateServer->getAuthData();
        if (empty($authData['username']) || empty($authData['password'])) {
            return false;
        }

        return true;
    }
}
