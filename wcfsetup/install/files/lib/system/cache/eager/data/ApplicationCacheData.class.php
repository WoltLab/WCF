<?php

namespace wcf\system\cache\eager\data;

use wcf\data\application\Application;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ApplicationCacheData
{
    public function __construct(
        /** @var array<int, Application> */
        public readonly array $application,
        /** @var array<string, int> */
        public readonly array $abbreviation,
    ) {
    }

    public function getApplication(int $packageID): ?Application
    {
        return $this->application[$packageID] ?? null;
    }

    public function getApplicationByAbbreviation(string $abbreviation): ?Application
    {
        $packageID = $this->abbreviation[$abbreviation] ?? null;
        if ($packageID === null) {
            return null;
        }

        return $this->getApplication($packageID);
    }

    public function getAbbreviationByPackageID(int $packageID): ?string
    {
        return \array_search($packageID, $this->abbreviation) ?: null;
    }
}
