<?php

namespace wcf\command\application;

use wcf\data\application\Application;
use wcf\data\application\ApplicationList;
use wcf\system\cache\eager\ApplicationCache;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Synchronizes the cookie domain for all installed apps.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class SynchronizeCookieDomain
{
    public function __invoke(): void
    {
        $sql = "UPDATE  wcf1_application
                SET     cookieDomain = ?
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $regex = new Regex(':[0-9]+');

        WCF::getDB()->beginTransaction();
        foreach ($this->getApplications() as $application) {
            $domainName = $application->domainName;
            if (\str_ends_with($regex->replace($domainName, ''), $application->cookieDomain)) {
                $domainName = $application->cookieDomain;
            }

            $statement->execute([
                $domainName,
                $application->packageID,
            ]);
        }
        WCF::getDB()->commitTransaction();

        $this->resetCache();
    }

    /**
     * @return Application[]
     */
    private function getApplications(): array
    {
        $applicationList = new ApplicationList();
        $applicationList->readObjects();

        return $applicationList->getObjects();
    }

    private function resetCache(): void
    {
        LanguageFactory::getInstance()->deleteLanguageCache();
        (new ApplicationCache())->rebuild();
    }
}
