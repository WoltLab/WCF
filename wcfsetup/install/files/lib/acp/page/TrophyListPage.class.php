<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\TrophyGridView;
use wcf\system\WCF;

/**
 * Trophy list page.
 *
 * @author Olaf Braun, Joshua Ruesweg
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 3.1
 *
 * @extends AbstractGridViewPage<TrophyGridView>
 */
final class TrophyListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.trophy.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canManageTrophy'];

    #[\Override]
    protected function createGridView(): TrophyGridView
    {
        return new TrophyGridView();
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
                FROM   wcf1_trophy
                WHERE  isLegacy = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);

        return $statement->fetchColumn() > 0;
    }
}
