<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\action\MediaEditAction;
use wcf\data\category\CategoryNodeTree;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\event\gridView\admin\MediaGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\FormBuilderDialogGridViewRowLink;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\FilesizeColumnRenderer;
use wcf\system\gridView\renderer\ILinkColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\admin\MediaInteractions;
use wcf\system\interaction\bulk\admin\MediaBulkInteractions;
use wcf\system\l10n\L10nStorage;
use wcf\system\request\LinkHandler;
use wcf\system\view\filter\BooleanFilter;
use wcf\system\view\filter\CategoryFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\L10nTextFilter;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractGridView<Media, MediaList>
 */
final class MediaGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('mediaID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(ObjectIdFilter::class)
                ->sortable(),
            GridViewColumn::for('preview')
                ->label('wcf.media.preview')
                ->renderer(
                    new class extends AbstractColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Media);

                            if ($row->isImage === 0) {
                                return \sprintf(
                                    '<a href="%s">%s</a>',
                                    StringUtil::encodeHTML($row->getLink()),
                                    $row->getElementTag(48)
                                );
                            }

                            return \sprintf(
                                '<a href="%s" data-type="image" data-fancybox="media" data-caption="%s">%s</a>',
                                StringUtil::encodeHTML($row->getFullSizeImageSource()),
                                StringUtil::encodeHTML($row->getTitle()),
                                $row->getElementTag(48)
                            );
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return 'gridView__column--image';
                        }
                    }
                ),
            GridViewColumn::for('filename')
                ->label('wcf.media.filename')
                ->titleColumn()
                ->filter(new TextFilter('filename', 'wcf.media.filename', 'media.filename'))
                ->unsafeDisableEncoding()
                ->renderer(new TruncatedTextColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'media.filename'),
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->filter(new L10nTextFilter(Media::getL10nDefinition(), 'title', 'title', 'wcf.global.title'))
                ->unsafeDisableEncoding()
                ->renderer(new TruncatedTextColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'title'),
            GridViewColumn::for('userID')
                ->label('wcf.media.uploader')
                ->filter(new UserFilter('userID', 'wcf.media.uploader', 'media.userID'))
                ->renderer(new UserLinkColumnRenderer(UserEditForm::class))
                ->sortable(sortByDatabaseColumn: 'media.username'),
            GridViewColumn::for('uploadTime')
                ->label('wcf.media.uploadTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(defaultSortOrder: 'DESC'),
            GridViewColumn::for('filesize')
                ->label('wcf.media.filesize')
                ->renderer(new FilesizeColumnRenderer())
                ->sortable(defaultSortOrder: 'DESC'),
            GridViewColumn::for('downloads')
                ->label('wcf.media.downloads')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(defaultSortOrder: 'DESC'),
            GridViewColumn::for('lastDownloadTime')
                ->label('wcf.media.lastDownloadTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(defaultSortOrder: 'DESC'),
        ]);

        $this->addAvailableFilter($this->getSearchFilter());
        // The category filter is unusable without any categories.
        $categories = (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator();
        if (\iterator_count($categories) > 0) {
            $this->addAvailableFilter(new CategoryFilter($categories, 'categoryID'));
        }
        $this->addAvailableFilter(new BooleanFilter('isImage', 'wcf.media.isImage'));

        $this->setInteractionProvider(new MediaInteractions());
        $this->setBulkInteractionProvider(new MediaBulkInteractions());
        // The identifier must differ from the `edit` interaction to not open the dialog twice.
        $this->addRowLink(new FormBuilderDialogGridViewRowLink(
            'edit-row',
            LinkHandler::getInstance()->getControllerLink(MediaEditAction::class, ['id' => '%s']),
            isAvailableCallback: static fn(Media $media) => $media->canManage()
        ));
        $this->setDefaultSortField('uploadTime');
        $this->setDefaultSortOrder('DESC');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.content.cms.canManageMedia');
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
    protected function getInitializedEvent(): MediaGridViewInitialized
    {
        return new MediaGridViewInitialized($this);
    }
}
