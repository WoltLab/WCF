<?php

namespace wcf\system\cache\eager;

use wcf\system\WCF;

/**
 * Eager cache of the database rows of the pages, their localized titles and
 * meta descriptions. It consists of plain arrays only, which allows the OPcache
 * to keep it in shared memory, the `Page` objects are created on demand by
 * `PageCache`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type PageRow array{
 *     pageID: int,
 *     parentPageID: ?int,
 *     identifier: string,
 *     name: string,
 *     pageType: string,
 *     isDisabled: int,
 *     isMultilingual: int,
 *     originIsSystem: int,
 *     packageID: int,
 *     applicationPackageID: ?int,
 *     overrideApplicationPackageID: ?int,
 *     controller: string,
 *     handler: string,
 *     controllerCustomURL: string,
 *     requireObjectID: int,
 *     hasFixedParent: int,
 *     lastUpdateTime: int,
 *     cssClassName: string,
 *     availableDuringOfflineMode: int,
 *     allowSpidersToIndex: int,
 *     excludeFromLandingPage: int,
 *     enableShareButtons: int,
 *     permissions: ?string,
 *     options: ?string,
 *     invertPermissions: int,
 * }
 *
 * @extends AbstractEagerCache<array{
 *     pages: array<int, PageRow>,
 *     identifier: array<string, int>,
 *     controller: array<string, int>,
 *     pageTitles: array<int, array<int, string>>,
 *     pageMetaDescriptions: array<int, array<int, string>>,
 *     landingPageID: ?int,
 * }>
 */
final class PageRowCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $data = [
            'pages' => [],
            'identifier' => [],
            'controller' => [],
            'pageTitles' => [],
            'pageMetaDescriptions' => [],
            'landingPageID' => null,
        ];

        $sql = "SELECT  landingPageID
                FROM    wcf1_application
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);
        $landingPageID = $statement->fetchSingleColumn();

        $sql = "SELECT  *
                FROM    wcf1_page";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $data['pages'][$row['pageID']] = $row;
            $data['identifier'][$row['identifier']] = $row['pageID'];
            $data['controller'][$row['controller']] = $row['pageID'];

            // The article list serves as the fallback for a missing landing page.
            if (
                $row['pageID'] === $landingPageID
                || ($data['landingPageID'] === null && $row['identifier'] === 'com.woltlab.wcf.ArticleList')
            ) {
                $data['landingPageID'] = $row['pageID'];
            }
        }

        $sql = "SELECT  pageID, languageID, title, metaDescription
                FROM    wcf1_page_content";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $languageID = $row['languageID'] ?? 0;
            $data['pageTitles'][$row['pageID']][$languageID] = $row['title'];
            $data['pageMetaDescriptions'][$row['pageID']][$languageID] = $row['metaDescription'];
        }

        return $data;
    }
}
