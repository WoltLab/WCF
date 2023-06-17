<?php

namespace wcf\acp\page;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Request;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\page\AbstractPage;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;

final class LicensePage extends AbstractPage
{
    // TODO: This should be the actual menu item.
    public $activeMenuItem = 'wcf.acp.menu.link.package';

    // TODO: This untyped array is awful, maybe use a custom object intead and
    // ask Valinor to hydrate it?
    private array $licenseData;

    // TODO: This could be part of the aforementioned custom object.
    private int $licenseNumber;

    public function readData()
    {
        parent::readData();

        // TODO: We actually need to fetch the data from the official package
        // update servers first, because we rely on the human readable names.

        // TODO: We should cache this data for a certain amount of time. There
        // also needs tobe a way to manually trigger a refresh in case of a
        // recent purchase.
        $this->licenseData = $this->fetchLicenseData();

        // TODO: This might need to use `getAuthData()` to inject the correct
        // credentials for the WoltLab Cloud.
        $this->licenseNumber = (new PackageUpdateServer(1))->loginUsername;

        // TODO: We need the data from the official package update servers to
        // determine the human readable values for each package and to filter
        // out those that are incompatible with the installed version.
    }

    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'licenseData' => $this->licenseData,
            'licenseNumber' => $this->licenseNumber,
        ]);
    }

    // TODO: This code was stol… liberated from "FirstTimeSetupLicenseForm" and
    // should propably be moved into a helper class. We might even want to refresh
    // the data with requests to the package servers to implicitly fetch the
    // latest purchases.
    private function fetchLicenseData(): array|object
    {
        $pus = new PackageUpdateServer(1);

        $request = new Request(
            'POST',
            'https://api.woltlab.com/2.0/customer/license/list.json',
            [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            \http_build_query([
                'licenseNo' => $pus->loginUsername,
                'serialNo' => $pus->loginPassword,
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
                            authCode: string,
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
}
