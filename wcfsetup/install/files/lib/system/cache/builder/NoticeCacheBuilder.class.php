<?php

namespace wcf\system\cache\builder;

use wcf\data\notice\NoticeList;

/**
 * Caches the enabled notices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NoticeCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $noticeList = new NoticeList();
        $noticeList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $noticeList->sqlOrderBy = 'showOrder ASC';
        $noticeList->readObjects();

        return $noticeList->getObjects();
    }
}
