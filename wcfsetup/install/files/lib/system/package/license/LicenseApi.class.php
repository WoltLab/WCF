<?php

namespace wcf\system\package\license;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Request;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\io\AtomicWriter;
use wcf\system\io\HttpFactory;
use wcf\system\package\license\exception\MissingCredentials;
use wcf\system\package\license\exception\ParsingFailed;
use wcf\system\WCF;

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
    private const LICENSE_FILE = \WCF_DIR . 'license.php';

    public function updateLicenseFile(?LicenseData $data): void
    {
        $creationDate = $data?->creationDate ?? new \DateTimeImmutable();

        $writer = new AtomicWriter(self::LICENSE_FILE);
        $writer->write("<?php /* {$creationDate->format('c')} */\n\n");
        $writer->write(\sprintf("return unserialize(%s);\n", \var_export(\serialize($data), true)));
        $writer->flush();

        WCF::resetZendOpcache(self::LICENSE_FILE);
    }

    private static function parseLicenseData(string $json): LicenseData
    {
        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->mapper()
                ->map(
                    LicenseData::class,
                    Source::json($json)
                );
        } catch (MappingError | InvalidSource $e) {
            throw new ParsingFailed($e);
        }
    }

    public static function fetchFromRemote(array $authData = []): LicenseData
    {
        if ($authData === []) {
            if (!self::hasLicenseCredentials()) {
                throw new MissingCredentials();
            }

            $authData = PackageUpdateServer::getWoltLabUpdateServer()->getAuthData();
        }

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

        return self::parseLicenseData($response->getBody());
    }

    public function readFromFile(): ?LicenseData
    {
        try {
            return require(self::LICENSE_FILE);
        } catch (\Throwable) {
            $this->clearLicenseFile();

            return null;
        }
    }

    public function clearLicenseFile(): void
    {
        $this->updateLicenseFile(null);
    }

    public static function hasLicenseCredentials(): bool
    {
        $authData = PackageUpdateServer::getWoltLabUpdateServer()->getAuthData();
        if (empty($authData['username']) || empty($authData['password'])) {
            return false;
        }

        return true;
    }

    public function getUpToDateLicenseData(): ?LicenseData
    {
        if (!LicenseApi::hasLicenseCredentials()) {
            return null;
        }

        $licenseData = $this->readFromFile();
        if (
            $licenseData === null
            // Cache valid license data for 2 minutes.
            || $licenseData->creationDate->getTimestamp() < (\TIME_NOW - 2 * 60)
        ) {
            $licenseData = LicenseApi::fetchFromRemote();
            $this->updateLicenseFile($licenseData);
        }
        return $licenseData;
    }
}
