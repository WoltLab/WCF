<?php

namespace wcf\system\cache\eager;

use wcf\system\WCF;

/**
 * Eager cache of the locations that have page notices, which allows pages
 * without notices to be rendered without querying the notices.
 *
 * The locations are stored as `pageID => pageObjectID => hasPublicNotices`
 * (public notices are notices that are not internal),
 * using `0` as the object id of pages that do not require an object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractEagerCache<array<int, array<int, bool>>>
 */
final class PageNoticeLocationCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $locations = [];

        $sql = "SELECT  pageID, COALESCE(pageObjectID, 0) AS pageObjectID, isInternal
                FROM    wcf1_page_notice";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $pageID = $row['pageID'];
            $pageObjectID = $row['pageObjectID'];
            $isPublic = (int)$row['isInternal'] === 0;

            $locations[$pageID][$pageObjectID] = ($locations[$pageID][$pageObjectID] ?? false) || $isPublic;
        }

        return $locations;
    }
}
