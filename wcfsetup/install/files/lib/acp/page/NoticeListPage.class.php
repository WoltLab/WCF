<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\NoticeGridView;
use wcf\system\WCF;

/**
 * Lists the available notices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<NoticeGridView>
 */
final class NoticeListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.notice.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.notice.canManageNotice'];

    #[\Override]
    protected function createGridView(): NoticeGridView
    {
        return new NoticeGridView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'hasLegacyObjects' => $this->hasLegacyObjects(),
        ]);
    }

    private function hasLegacyObjects(): bool
    {
        $sql = "SELECT COUNT(*) AS count
                FROM   wcf1_notice
                WHERE  isLegacy = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);

        return $statement->fetchColumn() > 0;
    }
}
