<?php

namespace wcf\system\package\license;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Request;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\io\HttpFactory;
use wcf\system\package\license\exception\MissingCredentials;
use wcf\system\package\license\exception\ParsingFailed;

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
    private readonly array $data;
    private readonly string $json;

    private const LICENSE_FILE = \WCF_DIR . 'license.php';

    private function __construct(string $json)
    {
        $this->json = $json;
        $this->data = $this->parseLicenseData($this->json);

        $this->updateLicenseFile();
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function updateLicenseFile(): void
    {
        @\file_put_contents(
            self::LICENSE_FILE,
            \sprintf(
                <<<'EOT'
                return '%s';
                EOT,
                $this->json,
            )
        );
    }

    private function parseLicenseData(string $json): array
    {
        try {
            /** @var array $result */
            $result = (new MapperBuilder())
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
                            ckeditorLicenseKey?: string,
                        },
                        pluginstore: array<string, string>,
                        woltlab: array<string, string>,
                    }
                    EOT,
                    Source::json($json)
                );
        } catch (MappingError $e) {
            throw new ParsingFailed($e);
        }

        return $result;
    }

    public static function fetchFromRemote(): LicenseApi
    {
        if (!self::hasLicenseCredentials()) {
            throw new MissingCredentials();
        }

        $authData = PackageUpdateServer::getWoltLabUpdateServer()->getAuthData();

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

        return new LicenseApi($response->getBody());
    }

    public static function readFromFile(): ?LicenseApi
    {
        if (!\is_readable(self::LICENSE_FILE)) {
            return null;
        }

        $content = \file_get_contents(self::LICENSE_FILE);

        try {
            return new LicenseApi($content);
        } catch (ParsingFailed) {
            return null;
        }
    }

    public static function hasLicenseCredentials(): bool
    {
        $authData = PackageUpdateServer::getWoltLabUpdateServer()->getAuthData();
        if (empty($authData['username']) || empty($authData['password'])) {
            return false;
        }

        return true;
    }
}
