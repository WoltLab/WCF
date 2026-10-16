<?php

namespace wcf\acp\page;

use wcf\page\AbstractListViewPage;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\MediaFileProcessor;
use wcf\system\listView\admin\MediaListView;
use wcf\system\WCF;

/**
 * Shows the list of media entries.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewPage<MediaListView>
 */
class MediaListPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.media.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageMedia'];

    #[\Override]
    protected function createListView(): MediaListView
    {
        return new MediaListView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        $processor = FileProcessor::getInstance()->getProcessorByName('com.woltlab.wcf.media');
        \assert($processor instanceof MediaFileProcessor);

        WCF::getTPL()->assign([
            'uploadButton' => $processor->toHtmlElement(),
        ]);
    }
}
