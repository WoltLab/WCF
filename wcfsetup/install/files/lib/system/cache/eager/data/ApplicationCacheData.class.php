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
        public readonly array $applications,
        /** @var array<string, int> */
        public readonly array $abbreviations,
    ) {
    }

    public function getApplication(int $packageID): ?Application
    {
        return $this->applications[$packageID] ?? null;
    }

    public function getApplicationByAbbreviation(string $abbreviation): ?Application
    {
        $packageID = $this->abbreviations[$abbreviation] ?? null;
        if ($packageID === null) {
            return null;
        }

        return $this->getApplication($packageID);
    }

    public function getAbbreviationByPackageID(int $packageID): ?string
    {
        return \array_search($packageID, $this->abbreviations) ?: null;
    }
}
