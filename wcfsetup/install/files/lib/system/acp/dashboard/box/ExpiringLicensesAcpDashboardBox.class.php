<?php

namespace wcf\system\acp\dashboard\box;

use wcf\data\package\PackageCache;
use wcf\system\package\license\LicenseApi;
use wcf\system\package\license\LicenseData;
use wcf\system\WCF;

/**
 * ACP dashboard box listing expired and expiring licenses.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ExpiringLicensesAcpDashboardBox extends AbstractAcpDashboardBox
{
    private ?LicenseData $licenseData;
    private array $expiredLicenses;

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.configuration.package.canEditServer');
    }

    #[\Override]
    public function hasContent(): bool
    {
        return $this->getExpiredLicenses() !== [];
    }

    private function getExpiredLicenses(): array
    {
        if (!isset($this->expiredLicenses)) {
            $licenseData = $this->getLicenseData();
            if ($licenseData === null) {
                $this->expiredLicenses = [];
                return $this->expiredLicenses;
            }

            $this->expiredLicenses = \array_filter(
                $licenseData->license['expiryDates'] ?? [],
                function ($date, $packageName) {
                    $expiryDate = \TIME_NOW + 7_776_000; //90 days
                    if (PackageCache::getInstance()->getPackageID($packageName) === null) {
                        // package not installed
                        return false;
                    }
                    return $date < $expiryDate;
                },
                \ARRAY_FILTER_USE_BOTH
            );
        }
        return $this->expiredLicenses;
    }

    private function getLicenseData(): ?LicenseData
    {
        if (!isset($this->licenseData)) {
            $licenseApi = new LicenseApi();
            $this->licenseData = $licenseApi->getUpToDateLicenseData();
        }

        return $this->licenseData;
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.acp.dashboard.box.expiringLicenses');
    }

    #[\Override]
    public function getContent(): string
    {
        $packages = [];
        foreach (\array_keys($this->getExpiredLicenses()) as $packageName) {
            $packages[$packageName] = PackageCache::getInstance()->getPackageByIdentifier($packageName);
        }

        $licenseNo = $this->getLicenseData()->getLicenseNumber();
        if ($licenseNo === null) {
            $ctaLink = 'https://www.woltlab.com/license-list/';
        } else {
            $ctaLink = \sprintf(
                'https://www.woltlab.com/license-extend/%s/',
                $licenseNo,
            );
        }

        return WCF::getTPL()->fetch('expiringLicensesAcpDashboardBox', 'wcf', [
            'packages' => $packages,
            'expiredLicenses' => \array_filter($this->getExpiredLicenses(), fn($date) => $date < \TIME_NOW),
            'expiringLicenses' => \array_filter($this->getExpiredLicenses(), fn($date) => $date >= \TIME_NOW),
            'ctaLink' => $ctaLink,
        ], true);
    }

    #[\Override]
    public function getName(): string
    {
        return 'com.woltlab.wcf.expiringLicenses';
    }
}
