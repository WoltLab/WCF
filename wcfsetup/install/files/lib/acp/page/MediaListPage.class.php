<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\MediaFileProcessor;
use wcf\system\gridView\admin\MediaGridView;
use wcf\system\WCF;

/**
 * Shows the list of media entries.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<MediaGridView>
 */
class MediaListPage extends AbstractGridViewPage
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
    protected function createGridView(): MediaGridView
    {
        return new MediaGridView();
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
