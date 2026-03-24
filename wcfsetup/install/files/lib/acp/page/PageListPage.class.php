<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\PageGridView;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows a list of pages.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<PageGridView>
 */
final class PageListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManagePage'];

    /**
     * display 'Add Page' dialog on load
     * @var int
     */
    public $showPageAddDialog = 0;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['showPageAddDialog'])) {
            $this->showPageAddDialog = 1;
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
            'showPageAddDialog' => $this->showPageAddDialog,
        ]);
    }

    #[\Override]
    protected function createGridView(): PageGridView
    {
        return new PageGridView();
    }
}
