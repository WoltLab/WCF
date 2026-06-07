<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\data\attachment\AdministrativeAttachment;
use wcf\data\attachment\AdministrativeAttachmentList;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\gridView\admin\AttachmentGridViewInitialized;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\FilesizeColumnRenderer;
use wcf\system\gridView\renderer\ILinkColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\interaction\admin\AttachmentInteractions;
use wcf\system\interaction\bulk\admin\AttachmentBulkInteractions;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of attachments.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<AdministrativeAttachment, AdministrativeAttachmentList>
 */
final class AttachmentGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('fileID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(ObjectIdFilter::class)
                ->sortable(),
            GridViewColumn::for('preview')
                ->label('wcf.attachment.preview')
                ->renderer(
                    new class extends AbstractColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof AdministrativeAttachment);

                            $isImage = $row->isImage;
                            $link = StringUtil::encodeHTML($row->getLink());
                            $fancyBox = $isImage ? ' data-type="image" data-fancybox="attachments" data-caption="' . StringUtil::encodeHTML($row->filename) . '"' : '';
                            if ($row->tinyThumbnailType) {
                                $thumbnailLink = \sprintf(
                                    '<img src="%s" class="attachmentTinyThumbnail" alt="">',
                                    StringUtil::encodeHTML($row->getThumbnailLink('tiny'))
                                );
                            } else {
                                $thumbnailLink = FontAwesomeIcon::fromValues($row->getIconName())->toHtml(64);
                            }

                            return <<<HTML
                                <a href="{$link}"{$fancyBox}>
                                    {$thumbnailLink}
                                </a>
                                HTML;
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return 'gridView__column--image';
                        }
                    }
                ),
            GridViewColumn::for('filename')
                ->label('wcf.attachment.filename')
                ->titleColumn()
                ->filter(new TextFilter('filename', 'wcf.attachment.filename', 'file_table.filename'))
                ->unsafeDisableEncoding()
                ->renderer(new TruncatedTextColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'file_table.filename'),
            GridViewColumn::for('container')
                ->label('wcf.acp.attachment.content')
                ->renderer(
                    new class extends DefaultColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof AdministrativeAttachment);

                            if ($row->getContainerObject() === null) {
                                return '';
                            }

                            return \sprintf(
                                '<a href="%s">%s</a>',
                                StringUtil::encodeHTML($row->getContainerObject()->getLink()),
                                StringUtil::encodeHTML($row->getContainerObject()->getTitle())
                            );
                        }
                    }
                ),
            GridViewColumn::for('username')
                ->label('wcf.user.username')
                ->filter(new UserFilter('username', 'wcf.user.username', 'user_table.userID'))
                ->renderer(
                    new class extends AbstractColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof AdministrativeAttachment);

                            if (!$row->userID) {
                                return WCF::getLanguage()->get('wcf.user.guest');
                            }

                            if (WCF::getSession()->hasPermission('admin.user.canEditUser')) {
                                return \sprintf(
                                    '<a href="%s">%s</a>',
                                    StringUtil::encodeHTML(
                                        LinkHandler::getInstance()->getControllerLink(
                                            UserEditForm::class,
                                            [
                                                'id' => $row->userID,
                                            ]
                                        )
                                    ),
                                    StringUtil::encodeHTML($row->username)
                                );
                            } else {
                                return StringUtil::encodeHTML($row->username);
                            }
                        }
                    }
                )
                ->sortable(sortByDatabaseColumn: 'user_table.username'),
            GridViewColumn::for('uploadTime')
                ->label('wcf.attachment.uploadTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(defaultSortOrder: 'DESC'),
            GridViewColumn::for('downloads')
                ->label('wcf.attachment.downloads')
                ->filter(IntegerFilter::class)
                ->renderer(new NumberColumnRenderer())
                ->sortable(defaultSortOrder: 'DESC'),
            GridViewColumn::for('filesize')
                ->label('wcf.attachment.filesize')
                ->renderer(new FilesizeColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'file_table.filesize', defaultSortOrder: 'DESC'),
            GridViewColumn::for('lastDownloadTime')
                ->label('wcf.attachment.lastDownloadTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(defaultSortOrder: 'DESC'),
        ]);

        $this->addAvailableFilters([
            new SelectFilter(
                $this->getAvailableFileTypes(),
                'fileType',
                'wcf.attachment.fileType',
                'file_table.mimeType'
            )
        ]);
        $interaction = new AttachmentInteractions();
        $this->setInteractionProvider($interaction);
        $this->setBulkInteractionProvider(new AttachmentBulkInteractions());
        $this->addRowLink(new GridViewRowLink(isLinkableObject: true));
        $this->setDefaultSortField('uploadTime');
        $this->setDefaultSortOrder('DESC');
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableFileTypes(): array
    {
        $objectTypeIDs = $this->getAvailableObjectTypeIDs();
        if ($objectTypeIDs === []) {
            return [];
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('attachment.fileID IS NOT NULL');
        $conditionBuilder->add('attachment.objectTypeID IN (?)', [$objectTypeIDs]);
        $conditionBuilder->add('attachment.tmpHash = ?', ['']);

        $sql = "SELECT    DISTINCT file_table.mimeType
                FROM      wcf1_attachment attachment
                LEFT JOIN wcf1_file file_table
                ON        (file_table.fileID = attachment.fileID)
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $fileTypes = $statement->fetchAll(\PDO::FETCH_COLUMN);

        \sort($fileTypes);

        return \array_combine($fileTypes, $fileTypes);
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.attachment.canManageAttachment');
    }

    #[\Override]
    protected function createObjectList(): AdministrativeAttachmentList
    {
        $list = new AdministrativeAttachmentList();

        $objectTypeIDs = $this->getAvailableObjectTypeIDs();
        if ($objectTypeIDs !== []) {
            $list->getConditionBuilder()->add('attachment.objectTypeID IN (?)', [$objectTypeIDs]);
        } else {
            $list->getConditionBuilder()->add('1=0');
        }
        $list->getConditionBuilder()->add('attachment.tmpHash = ?', ['']);

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): AttachmentGridViewInitialized
    {
        return new AttachmentGridViewInitialized($this);
    }

    /**
     * @return list<int>
     */
    private function getAvailableObjectTypeIDs(): array
    {
        $objectTypeIDs = [];
        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.attachment.objectType') as $objectType) {
            if (!$objectType->private) {
                $objectTypeIDs[] = $objectType->objectTypeID;
            }
        }

        return $objectTypeIDs;
    }
}
