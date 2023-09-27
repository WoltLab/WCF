<?php

namespace wcf\system\package\license;

/**
 * Provides structured access to the license data.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class LicenseData
{
    /**
     * @param   array{
     *              authCode?: string,
     *              licenseID?: int,
     *              type: string,
     *              expiryDates?: array<string, int>,
     *              ckeditorLicenseKey?: string,
     *          } $license
     * @param   array<string,string> $pluginstore 
     * @param   array<string,string> $woltlab 
     */
    public function __construct(
        public readonly array $license,
        public readonly array $pluginstore,
        public readonly array $woltlab,
    )
    {
    }

    public function getLicenseNumber(): ?string
    {
        return $this->license['licenseID'] ?? null;
    }

    public function getLicenseType(): string
    {
        return $this->license['type'];
    }
}
