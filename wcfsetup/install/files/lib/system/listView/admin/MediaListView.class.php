<?php

namespace wcf\system\listView\admin;

use wcf\data\category\CategoryNodeTree;
use wcf\data\DatabaseObjectList;
use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\event\listView\admin\MediaListViewInitialized;
use wcf\system\interaction\admin\MediaInteractions;
use wcf\system\interaction\bulk\admin\MediaBulkInteractions;
use wcf\system\l10n\L10nStorage;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\ListViewSortField;
use wcf\system\view\filter\BooleanFilter;
use wcf\system\view\filter\CategoryFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;

/**
 * List view for the list of media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<Media, MediaList>
 */
class MediaListView extends AbstractListView
{
    public function __construct()
    {
        $this->addAvailableSortFields([
            new ListViewSortField('uploadTime', 'wcf.media.uploadTime', defaultSortOrder: 'DESC'),
            new ListViewSortField('title', 'wcf.global.title', 'title'),
            new ListViewSortField('filename', 'wcf.media.filename'),
            new ListViewSortField('filesize', 'wcf.media.filesize', defaultSortOrder: 'DESC'),
            new ListViewSortField('downloads', 'wcf.media.downloads', defaultSortOrder: 'DESC'),
            new ListViewSortField('lastDownloadTime', 'wcf.media.lastDownloadTime', defaultSortOrder: 'DESC'),
        ]);

        $this->addAvailableFilters([
            $this->getSearchFilter(),
            new CategoryFilter(
                (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator(),
                'categoryID'
            ),
            new UserFilter('userID', 'wcf.media.uploader'),
            new BooleanFilter('isImage', 'wcf.media.isImage'),
        ]);

        $this->setInteractionProvider(new MediaInteractions());
        $this->setBulkInteractionProvider(new MediaBulkInteractions());
        $this->setItemsPerPage(30);
        $this->setDefaultSortField('uploadTime');
        $this->setDefaultSortOrder('DESC');
        $this->setCssClassName('mediaList');
    }

    #[\Override]
    protected function createObjectList(): MediaList
    {
        $list = new MediaList();

        // required to sort by the localized title
        if ($list->sqlSelects !== '') {
            $list->sqlSelects .= ',';
        }
        $list->sqlSelects .= (new L10nStorage(Media::getL10nDefinition()))->getSubSelect('title', 'media') . ' AS title';

        if (WCF::getSession()->hasPermission('admin.content.cms.canOnlyAccessOwnMedia')) {
            $list->getConditionBuilder()->add('media.userID = ?', [WCF::getUser()->userID]);
        }

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.content.cms.canManageMedia');
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_mediaListViewItems', ['view' => $this]);
    }

    private function getSearchFilter(): TextFilter
    {
        return new class('search', 'wcf.global.search') extends TextFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                \assert($list instanceof MediaList);

                $list->addSearchConditions($value);
            }
        };
    }

    #[\Override]
    protected function getInitializedEvent(): MediaListViewInitialized
    {
        return new MediaListViewInitialized($this);
    }
}
