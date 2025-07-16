<?php

namespace wcf\system\cache\eager;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeList;

/**
 * Caches the enabled notices.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<array<int, Notice>>
 */
final class NoticeCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $noticeList = new NoticeList();
        $noticeList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $noticeList->getConditionBuilder()->add('isLegacy = ?', [0]);
        $noticeList->sqlOrderBy = 'showOrder ASC';
        $noticeList->readObjects();

        return $noticeList->getObjects();
    }
}
