<?php

namespace wcf\system\listView\user;

use wcf\action\MediaEditAction;
use wcf\data\category\CategoryNodeTree;
use wcf\data\DatabaseObjectList;
use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\event\listView\user\MediaManagerListViewInitialized;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\MediaFileProcessor;
use wcf\system\interaction\admin\MediaInteractions;
use wcf\system\interaction\bulk\user\MediaManagerBulkInteractions;
use wcf\system\l10n\L10nStorage;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\ListViewPrimaryButton;
use wcf\system\listView\ListViewSortField;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\view\filter\CategoryFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * List view of the media manager that is used to insert media files into
 * editors or to select media files.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<Media, MediaList>
 */
class MediaManagerListView extends AbstractListView
{
    public const MODE_EDITOR = 'editor';
    public const MODE_SELECT = 'select';

    public function __construct(
        public readonly string $mode = self::MODE_EDITOR,
        public readonly int $imagesOnly = 0,
    ) {
        if ($this->mode !== self::MODE_EDITOR && $this->mode !== self::MODE_SELECT) {
            throw new \InvalidArgumentException("Unknown mode '{$this->mode}'.");
        }

        $this->addAvailableSortFields([
            new ListViewSortField('uploadTime', 'wcf.media.uploadTime', defaultSortOrder: 'DESC'),
            new ListViewSortField('title', 'wcf.global.title', 'title'),
            new ListViewSortField('filename', 'wcf.media.filename'),
        ]);

        $this->addAvailableFilters([
            $this->getSearchFilter(),
            new CategoryFilter(
                (new CategoryNodeTree('com.woltlab.wcf.media.category'))->getIterator(),
                'categoryID'
            ),
        ]);

        $this->setInteractionProvider(new MediaInteractions());
        if ($this->mode === self::MODE_EDITOR) {
            $this->setBulkInteractionProvider(new MediaManagerBulkInteractions());
        }

        if (WCF::getSession()->hasPermission('admin.content.cms.canManageMedia')) {
            $processor = FileProcessor::getInstance()->getProcessorByName('com.woltlab.wcf.media');
            \assert($processor instanceof MediaFileProcessor);

            // The upload element is hidden and triggered by the primary button.
            $this->setPrimaryButton(ListViewPrimaryButton::forButton(
                'wcf.global.button.upload',
                FontAwesomeIcon::fromValues('upload'),
                'mediaManager__uploadButton'
            ));
            $this->setAdditionalHeaderContent(\sprintf(
                '<div class="mediaManager__upload" data-edit-endpoint="%s" hidden>%s</div>',
                StringUtil::encodeHTML(
                    LinkHandler::getInstance()->getControllerLink(MediaEditAction::class, ['id' => '%s'])
                ),
                $processor->toHtmlElement(imagesOnly: $this->imagesOnly !== 0)
            ));
        }

        $this->setItemsPerPage(30);
        $this->setDefaultSortField('uploadTime');
        $this->setDefaultSortOrder('DESC');
        $this->setCssClassName('mediaList mediaManager');
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
        if ($this->imagesOnly !== 0) {
            $list->getConditionBuilder()->add('media.isImage = ?', [1]);
        }

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.content.cms.canManageMedia')
            || WCF::getSession()->hasPermission('admin.content.cms.canUseMedia');
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'mode' => $this->mode,
            'imagesOnly' => $this->imagesOnly,
        ];
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_mediaManagerListItems', ['view' => $this]);
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
    protected function getInitializedEvent(): MediaManagerListViewInitialized
    {
        return new MediaManagerListViewInitialized($this);
    }
}
